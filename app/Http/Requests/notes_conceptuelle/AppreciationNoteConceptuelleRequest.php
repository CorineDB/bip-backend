<?php

namespace App\Http\Requests\notes_conceptuelle;

use App\Repositories\DocumentRepository;
use App\Repositories\Contracts\NoteConceptuelleRepositoryInterface;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppreciationNoteConceptuelleRequest extends FormRequest
{
    protected $champs = [];

    protected $champsAEvaluer = [];

    protected $appreciations = [];

    protected $champsDejaPassés = [];
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
        // Si evaluer = true : on doit évaluer TOUS les champs à évaluer
        // Si evaluer = false : on peut évaluer partiellement (brouillon)
        $minChamps = $evaluer ? count($this->champsAEvaluer) : 0;
        $maxChamps = count($this->champsAEvaluer);

        return [
            'evaluer' => 'required|boolean',

            'evaluations_champs' => 'required_unless:evaluer,0|array|min:' . $minChamps . '|max:' . $maxChamps,
            'evaluations_champs.*.champ_id' => ["required_with:evaluations_champs", "in:" . implode(",", $this->champsAEvaluer), Rule::exists("champs", "id",)],
            'evaluations_champs.*.appreciation' => 'required_with:evaluations_champs|in:' . implode(",", $this->appreciations),
            'evaluations_champs.*.commentaire' => 'required_unless:evaluer,0|string|min:10',

            /*'numero_dossier'            => 'required_unless:evaluer,0',//'required_unless:evaluer,0|string|max:100',
            'numero_contrat'            => 'required_unless:evaluer,0|string|max:100',*/

            // ✅ accept_term doit être "true" si est_soumise est true
            'accept_term'               => 'required_unless:evaluer,0|boolean' . ($evaluer  ? '|accepted' : ''),
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'evaluations_champs.required' => 'Les évaluations des champs sont obligatoires.',
            'evaluations_champs.array' => 'Les évaluations doivent être un tableau.',
            'evaluations_champs.min' => 'Au moins une évaluation de champ est requise.',
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
        $canevas = app()->make(DocumentRepository::class)->getModel()
            ->where('type', 'checklist')
            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-appreciation-note-conceptuelle'))
            ->orderBy('created_at', 'desc')->first();

        $evaluationConfigs = $canevas?->evaluation_configs;

        $this->appreciations = collect($evaluationConfigs['guide_notation'] ?? [])->pluck('appreciation')->toArray();

        $this->champs = $canevas->all_champs->pluck("id")->toArray();

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

        // Les champs à évaluer sont tous les champs SAUF ceux déjà passés
        // Si pas de parent, on évalue tous les champs (première évaluation)
        $this->champsAEvaluer = array_diff($this->champs, $this->champsDejaPassés);
    }
}
