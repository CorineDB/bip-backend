<?php

namespace App\Http\Requests\faisabilite;

use App\Repositories\DocumentRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvaluerTdrsFaisabiliteRequest extends FormRequest
{
    protected $champs = [];

    protected $appreciations = [];

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
        return [
            'evaluations_champs' => 'required|array|min:'. count($this->champs),
            'evaluations_champs.*.champ_id' => ["required", "in:".implode(",", $this->champs), Rule::exists("champs", "id",)],
            'evaluations_champs.*.appreciation' => 'required|in:'.implode(",", $this->appreciations),
            'evaluations_champs.*.commentaire' => 'required|string|min:10',

            'numero_dossier'            => 'required|string|max:100',
            'numero_contrat'            => 'required|string|max:100',

            // ✅ accept_term doit être "true" si est_soumise est true
            'accept_term'               => 'required|accepted',
            //'finaliser' => 'required|boolean',
            //'action' => 'nullable|string|in:reviser,abandonner'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'evaluations_champs.required' => 'Au moins une évaluation de champ est requise.',
            'evaluations_champs.array' => 'Les évaluations doivent être sous forme de tableau.',
            'evaluations_champs.min' => 'Au moins une évaluation de champ est requise.',
            'evaluations_champs.*.champ_id.required' => 'L\'ID du champ est obligatoire.',
            'evaluations_champs.*.champ_id.integer' => 'L\'ID du champ doit être un nombre entier.',
            'evaluations_champs.*.champ_id.exists' => 'Le champ spécifié n\'existe pas.',
            'evaluations_champs.*.appreciation.required' => 'L\'appréciation est obligatoire.',
            'evaluations_champs.*.appreciation.in' => 'L\'appréciation doit être: passe, retour ou non_accepte.',
            'evaluations_champs.*.commentaire.string' => 'Le commentaire doit être du texte.',
            'evaluations_champs.*.commentaire.max' => 'Le commentaire ne peut dépasser 1000 caractères.',
            'commentaire.string' => 'Le commentaire global doit être du texte.',
            'commentaire.max' => 'Le commentaire global ne peut dépasser 2000 caractères.',
            'action.in' => 'Action invalide. Actions possibles: abandonner.'
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
            'action' => 'action à effectuer'
        ];
    }

    public function prepareForValidation(){
        $canevas = app()->make(DocumentRepository::class)->getModel()
                                            ->where('type', 'checklist')
                                            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-appreciation-tdrs-faisabilite'))
                                            ->orderBy('created_at', 'desc')->first();

        $evaluationConfigs = $canevas?->evaluation_configs;

        $this->appreciations = collect($evaluationConfigs['options_notation'] ?? [])->pluck('appreciation')->toArray();

        $this->champs = $canevas->all_champs->pluck("id")->toArray();
    }
}
