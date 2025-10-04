<?php

namespace App\Http\Requests\tdrs;

use App\Repositories\DocumentRepository;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EvaluerTdrsRequest extends FormRequest
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
        $evaluer = $this->input('evaluer', true);

        return [
            'evaluer' => 'required|boolean',
            //'evaluations_champs' => 'required_unless:evaluer,0|array|min:'. count($this->champs),

            'evaluations_champs' => 'required_unless:evaluer,0|array|min:' . $evaluer  ? count($this->champs) : 0 . ($evaluer  ?  "|max:" . count($this->champs) : ""),

            'evaluations_champs.*.champ_id' => ["required_with:evaluations_champs", "in:".implode(",", $this->champs), Rule::exists("champs", "id",)],
            'evaluations_champs.*.appreciation' => 'required_with:evaluations_champs|in:'.implode(",", $this->appreciations),
            'evaluations_champs.*.commentaire' => 'required_unless:evaluer,0|string|min:10',

            'numero_dossier'            => 'required_unless:evaluer,0|string|max:100',
            'numero_contrat'            => 'required_unless:evaluer,0|string|max:100',

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

    public function prepareForValidation(){
        $canevas = app()->make(DocumentRepository::class)->getModel()
                                            ->where('type', 'checklist')
                                            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-appreciation-tdrs-prefaisabilite'))
                                            ->orderBy('created_at', 'desc')->first();

        $evaluationConfigs = $canevas?->evaluation_configs;

        $this->appreciations = collect($evaluationConfigs['options_notation'] ?? [])->pluck('appreciation')->toArray();

        $this->champs = $canevas->all_champs->pluck("id")->toArray();
    }
}
