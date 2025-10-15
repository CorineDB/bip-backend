<?php

namespace App\Http\Requests\evaluations;

use App\Models\CategorieCritere;
use App\Models\Evaluation;
use App\Models\IdeeProjet;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ModifierEvaluationClimatiqueRequest extends FormRequest
{
    protected $categorieCritere;
    protected $evaluation;
    protected $ideeProjet;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $ideeProjetId = $this->route('ideeProjetId');

        // Charger l'idée de projet
        $this->ideeProjet = IdeeProjet::find($ideeProjetId);

        // Charger l'évaluation climatique pour cette idée de projet
        $this->evaluation = Evaluation::where('projetable_type', 'App\Models\IdeeProjet')
            ->where('projetable_id', $ideeProjetId)
            ->where('type_evaluation', 'climatique')
            ->first();

        // Charger la catégorie de critères pour l'évaluation climatique
        $this->categorieCritere = CategorieCritere::where('type', 'evaluation-preliminaire-multi-projet-impact-climatique')
            ->orWhere('slug', 'evaluation-climatique')
            ->first();
    }

    public function rules(): array
    {
        $ideeProjetId = $this->route('ideeProjetId');

        return [
            'reponses' => 'required|array|min:1',
            'reponses.*.critere_id' => [
                'required',
                Rule::exists('criteres', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($ideeProjetId) {
                    $this->validateCritereForResponsable($attribute, $value, $fail, $ideeProjetId);
                }
            ],
            'reponses.*.notation_id' => [
                'nullable',
                Rule::exists('notations', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    if ($value) { // Si une notation est fournie, la valider
                        $this->validateNotationForCritere($attribute, $value, $fail);
                    }
                }
            ],
            'reponses.*.commentaire' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'reponses.required' => 'Les réponses sont obligatoires.',
            'reponses.array' => 'Les réponses doivent être un tableau.',
            'reponses.*.critere_id.required' => 'L\'ID du critère est obligatoire.',
            'reponses.*.critere_id.exists' => 'Le critère spécifié n\'existe pas.',
            'reponses.*.notation_id.exists' => 'La notation spécifiée n\'existe pas.',
            'reponses.*.commentaire.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
        ];
    }

    /**
     * Valide que le critère appartient à l'évaluation climatique et que l'utilisateur
     * connecté est bien le responsable de l'idée de projet.
     */
    private function validateCritereForResponsable($attribute, $critereId, $fail, $ideeProjetId)
    {
        $responsableId = auth()->id();

        if (!$this->evaluation) {
            $fail('Aucune évaluation climatique trouvée pour cette idée de projet.');
            return;
        }

        if (!$this->ideeProjet) {
            $fail('Idée de projet non trouvée.');
            return;
        }

        // Vérifier que l'utilisateur connecté est bien le responsable de l'idée de projet
        if ($this->ideeProjet->responsableId !== $responsableId) {
            $fail('Vous n\'êtes pas le responsable de cette idée de projet.');
            return;
        }

        // Vérifier que le critère appartient à la bonne catégorie pour l'évaluation climatique
        if ($this->categorieCritere) {
            $critere = \App\Models\Critere::find($critereId);
            if ($critere && $critere->categorie_critere_id !== $this->categorieCritere->id) {
                $fail('Ce critère n\'appartient pas à la catégorie d\'évaluation climatique.');
            }
        }
    }

    /**
     * Valide que la notation appartient soit au critère spécifique, soit à sa catégorie de critères.
     */
    private function validateNotationForCritere($attribute, $notationId, $fail)
    {
        // Extraire l'index pour récupérer le critere_id correspondant
        preg_match('/reponses\.(\d+)\.notation_id/', $attribute, $matches);
        if (!isset($matches[1])) {
            $fail('Erreur de validation interne.');
            return;
        }

        $index = $matches[1];
        $critereId = $this->input("reponses.{$index}.critere_id");

        if (!$critereId) {
            return; // Le critere_id sera validé par sa propre règle
        }

        // Récupérer le critère et sa catégorie
        $critere = \App\Models\Critere::find($critereId);
        if (!$critere) {
            return; // Le critère sera validé par sa propre règle
        }

        // Vérifier que la notation appartient soit au critère spécifique, soit à sa catégorie
        $notationExists = \App\Models\Notation::where('id', $notationId)
            ->where(function ($query) use ($critereId, $critere) {
                $query->where('critere_id', $critereId) // Notation spécifique au critère
                      ->orWhere(function ($subQuery) use ($critere) {
                          $subQuery->where('categorie_critere_id', $critere->categorie_critere_id)
                                   ->whereNull('critere_id'); // Notation générale de la catégorie
                      });
            })
            ->exists();

        if (!$notationExists) {
            $fail('La notation sélectionnée n\'est pas compatible avec ce critère.');
        }
    }
}
