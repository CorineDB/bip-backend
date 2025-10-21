<?php

namespace App\Http\Requests\notes_conceptuelle;

use App\Models\Champ;
use App\Repositories\DocumentRepository;
use App\Repositories\Contracts\NoteConceptuelleRepositoryInterface;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;

class AppreciationNoteConceptuelleRequest extends FormRequest
{
    protected $canevas = null;

    protected $champs = [];

    protected $champsAEvaluer = [];

    protected $appreciations = [];

    protected $champsDejaPassés = [];

    protected $champsNonPasses = [];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //auth()->check() && in_array(auth()->user()->type, ['dgpd', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $evaluer = $this->input('evaluer', true);

        // Déterminer le nombre minimum et maximum de champs à évaluer
        // Si evaluer = true : on doit évaluer AU MINIMUM tous les champs non-passés
        // Si evaluer = false : on peut évaluer partiellement (brouillon)
        $minChamps = $evaluer ? count($this->champsNonPasses) : 0;
        $maxChamps = count($this->champs); // Maximum = tous les champs du canevas

        return [
            'evaluer' => 'required|boolean',

            'evaluations_champs' => 'required_unless:evaluer,0|array|min:' . $minChamps . '|max:' . $maxChamps,
            'evaluations_champs.*.champ_id' => ['required_with:evaluations_champs', 'in:' . implode(',', $this->champsAEvaluer), new HashedExists(Champ::class)],
            'evaluations_champs.*.appreciation' => ($evaluer ? 'required' : 'nullable') . '|in:' . implode(",", $this->appreciations),
            'evaluations_champs.*.commentaire' => 'nullable|string|min:10',

            // ✅ accept_term doit être "true" si est_soumise est true
            'accept_term'               => 'required_unless:evaluer,0|boolean' . ($evaluer  ? '|accepted' : ''),
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $evaluer = $this->input('evaluer', false);
            $evaluationsChamps = $this->input('evaluations_champs', []);

            // Si evaluer = false, on n'impose pas les validations strictes
            if (!$evaluer) {
                return;
            }

            // 1. Vérifier que TOUS les champs du canevas ont été évalués
            $champsEvaluesIds = collect($evaluationsChamps)->pluck('champ_id')->toArray();
            //$champsManquants = array_diff($this->champs, $champsEvaluesIds);

            $champs = $this->canevas?->all_champs->pluck("id")->toArray();

            $champsManquants = array_diff($champs, $champsEvaluesIds);

            if (!empty($champsManquants)) {
                $validator->errors()->add(
                    'evaluations_champs',
                    'Tous les champs du canevas doivent être évalués avant de finaliser. Il manque ' . count($champsManquants) . ' champ(s).'
                );
            }

            // 2. Vérifier que les champs soumis ont une appréciation valide
            // (Important si un champ "passé" a été modifié à null en brouillon)
            foreach ($evaluationsChamps as $index => $evaluation) {
                $champId = $evaluation['champ_id'] ?? null;
                $appreciation = $evaluation['appreciation'] ?? null;
                $commentaire = $evaluation['commentaire'] ?? null;

                // Si l'appréciation est vide, c'est une erreur en mode finalisation
                if (empty($appreciation)) {
                    $validator->errors()->add(
                        "evaluations_champs.{$index}.appreciation",
                        "L'appréciation est obligatoire pour tous les champs lors de la finalisation."
                    );
                }

                // Si l'appréciation n'est pas "passe", le commentaire est obligatoire
                if ($appreciation && $appreciation !== 'passe' && empty($commentaire)) {
                    $validator->errors()->add(
                        "evaluations_champs.{$index}.commentaire",
                        "Un commentaire est obligatoire pour les appréciations autres que 'Passé'."
                    );
                }
            }
        });
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        $minRequis = count($this->champsNonPasses);

        return [
            'evaluations_champs.required' => 'Les évaluations des champs sont obligatoires.',
            'evaluations_champs.array' => 'Les évaluations doivent être un tableau.',
            'evaluations_champs.min' => $minRequis > 0
                ? "Vous devez évaluer au minimum {$minRequis} champ(s) (les champs non passés)."
                : 'Au moins une évaluation de champ est requise.',
            'evaluations_champs.*.appreciation.required' => 'Une appréciation est obligatoire pour chaque champ.',
            'evaluations_champs.*.appreciation.in' => 'L\'appréciation doit être : Passe, Retour, ou Non accepté.',
            'evaluations_champs.*.commentaire.required' => 'Un commentaire est obligatoire pour chaque évaluation.',
            'evaluations_champs.*.commentaire.min' => 'Le commentaire doit contenir au moins 10 caractères.',
            'evaluations_champs.*.commentaire.max' => 'Le commentaire ne peut dépasser 500 caractères.',
            'commentaire.max' => 'Le commentaire global ne peut dépasser 2000 caractères.',
            'finaliser.required' => 'Vous devez spécifier si l\'évaluation doit être finalisée.',
            'finaliser.boolean' => 'La finalisation doit être vraie ou fausse.',
            'action.in' => 'L\'action doit être : réviser ou abandonner.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'evaluations_champs' => 'évaluations des champs',
            'commentaire' => 'commentaire global',
            'finaliser' => 'finalisation',
            'action' => 'action'
        ];
    }

    public function prepareForValidation()
    {
        $this->canevas = $canevas = app()->make(DocumentRepository::class)->getModel()
            ->where('type', 'checklist')
            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-appreciation-note-conceptuelle'))
            ->orderBy('created_at', 'desc')->first();

        $evaluationConfigs = $canevas?->evaluation_configs;

        $this->appreciations = collect($evaluationConfigs['guide_notation'] ?? [])->pluck('appreciation')->toArray();

        //$this->champs = $canevas->all_champs->pluck("id")->toArray();
        $this->champs = $canevas->all_champs->pluck("hashed_id")->toArray();

        // Récupérer l'évaluation en cours pour identifier les champs déjà passés
        // SEULEMENT si la note a un parent (réévaluation après retour/rejet)
        $noteId = $this->route('noteId') ?? null;

        if ($noteId) {
            $noteRepository = app()->make(NoteConceptuelleRepositoryInterface::class);
            $noteConceptuelle = $noteRepository->find($noteId);

            // Vérifier si c'est une réévaluation (la note a un parent)
            if ($noteConceptuelle && $noteConceptuelle->parentId) {
                // Récupérer l'évaluation en cours
                $evaluationEnCours = $noteConceptuelle->evaluationEnCours();

                if ($evaluationEnCours && !empty($evaluationEnCours->evaluation)) {
                    // Récupérer les champs déjà marqués comme "passé" depuis le JSON evaluation
                    $champsEvalues = $evaluationEnCours->evaluation['champs_evalues'] ?? [];

                    $this->champsDejaPassés = collect($champsEvalues)
                        ->filter(function ($champ) {
                            return isset($champ['appreciation']) && $champ['appreciation'] === 'passe';
                        })
                        ->pluck('champ_id')
                        ->toArray();
                }
            }
        }

        // Calculer les champs non passés (pour le minimum requis)
        $this->champsNonPasses = array_diff($this->champs, $this->champsDejaPassés);

        // Tous les champs peuvent être soumis (même ceux déjà passés peuvent être réévalués)
        $this->champsAEvaluer = $this->champs;
    }
}
