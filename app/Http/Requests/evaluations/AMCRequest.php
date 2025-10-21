<?php

namespace App\Http\Requests\evaluations;

use App\Models\CategorieCritere;
use App\Models\Critere;
use App\Models\Evaluation;
use App\Models\Notation;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AMCRequest extends FormRequest
{
    protected $categorieCritere;
    protected $evaluation;

    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $ideeProjetId = $this->route('ideeProjetId');

        // Charger la catégorie de critères pour l'évaluation climatique
        $this->categorieCritere = CategorieCritere::where('slug', 'grille-analyse-multi-critere')
            ->first();
    }

    public function rules(): array
    {
        $ideeProjetId = $this->route('ideeProjetId');

        return [
            'reponses' => ["required", "array", "min:1"],
            'reponses.*.critere_id' => [
                'required', new HashedExists(Critere::class),
                //Rule::exists('criteres', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) use ($ideeProjetId) {
                    $this->validateCritereInEvaluation($attribute, $value, $fail, $ideeProjetId);
                }
            ],
            'reponses.*.notation_id' => [
                'required',new HashedExists(Notation::class),
                //Rule::exists('notations', 'id')->whereNull('deleted_at'),
                function ($attribute, $value, $fail) {
                    $this->validateNotationForCritere($attribute, $value, $fail);
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
            'reponses.*.notation_id.required' => 'La notation est obligatoire.',
            'reponses.*.notation_id.exists' => 'La notation spécifiée n\'existe pas.',
            'reponses.*.commentaire.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
        ];
    }

    /**
     * Valide que le critère appartient bien à l'évaluation climatique de cette idée de projet
     * et que l'utilisateur connecté est assigné à ce critère.
     */
    private function validateCritereInEvaluation($attribute, $critereId, $fail, $ideeProjetId)
    {

        // Vérifier que le critère appartient à la bonne catégorie pour l'évaluation climatique
        if ($this->categorieCritere) {
            $critere = \App\Models\Critere::findByHashedId($critereId);

            if (($critere && (($critere->categorie_critere_id !== $this->categorieCritere->id)))) {
                $fail("Ce critère n'est pas un critere d'analyse multi-critere.");
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
        $critere = \App\Models\Critere::findByHashedId($critereId);
        if (!$critere) {
            return; // Le critère sera validé par sa propre règle
        }

        // Vérifier que la notation appartient soit au critère spécifique, soit à sa catégorie
        $notationExists = \App\Models\Notation::findByHashedId('id', $notationId)
            ->where(function ($query) use ($critereId, $critere) {
                $query->where('categorie_critere_id', $critere->categorie_critere_id); // Notation spécifique au critère;
            })
            ->exists();

        if (!$notationExists) {
            $fail('La notation sélectionnée n\'est pas compatible avec ce critère.');
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->categorieCritere) {
                $validator->errors()->add('reponses', 'Catégorie de critères introuvable.');
                return;
            }

            $reponses = $this->input('reponses', []);

            // Récupérer tous les critères attendus (id => titre)
            $criteresAttendus = \App\Models\Critere::where('categorie_critere_id', $this->categorieCritere->id)
            ->whereRaw('LOWER(intitule) NOT LIKE ?', ['%impact climatique%'])/*
            ->where("intitule", "<>", "Impact climatique") */
                ->pluck('intitule', 'id') // id => intitule
                ->toArray();

            // IDs soumis
            $criteresSoumis = collect($reponses)->pluck('critere_id')->filter()->unique()->toArray();

            // Critères manquants
            $manquants = array_diff_key($criteresAttendus, array_flip($criteresSoumis));

            if (count($manquants) > 0) {
                $validator->errors()->add(
                    'reponses',
                    'Tous les critères obligatoires doivent être évalués. Il manque : ' . implode(', ', $manquants)
                );
            }
        });
    }
}
