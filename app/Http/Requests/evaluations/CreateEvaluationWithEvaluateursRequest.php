<?php

namespace App\Http\Requests\evaluations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEvaluationWithEvaluateursRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
        return auth()->user()->hasPermissionTo("effectuer-evaluation-climatique-idee-projet") || (auth()->user()->hasPermissionTo("effectuer-evaluation-climatique-idee-projet"));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type_evaluation' => ["required", "string", "in:climatique,amc,note_conceptuelle"],/*
            'date_debut_evaluation' => 'required|date',
            'date_fin_evaluation' => 'nullable|date|after:date_debut_evaluation',
            'projetable_type' => 'required|string',
            'projetable_id' => 'required|integer|exists:projets,id',
            'evaluateur_principal_id' => 'nullable|integer|exists:users,id',
            'evaluation' => 'nullable|array',*/
            'commentaire' => 'nullable|string|max:1000',

            'notation_id' => 'required|integer|exists:notations,id',
            'note' => 'nullable|string|max:500',

            // Assignation des évaluateurs aux critères
            'criteres' => 'required|array|min:1',
            'criteres.*.critere_id' => ['required', Rule::exists('criteres', 'id')->whereNull('deleted_at')],
            'criteres.*.critere_id' => ['required', Rule::exists('criteres', 'id')->whereNull('deleted_at')],
            /*'criteres.*.evaluateur_id' => 'required|integer|exists:users,id',
            'criteres.*.categorie_critere_id' => ["required","integer", Rule::exists('categories_critere', 'id')->whereNull('deleted_at')],*/
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'date_debut_evaluation.required' => 'La date de début d\'évaluation est obligatoire.',
            'date_fin_evaluation.after' => 'La date de fin doit être postérieure à la date de début.',
            'projetable_id.exists' => 'Le projet sélectionné n\'existe pas.',
            'evaluateurs_criteres.required' => 'Au moins un évaluateur doit être assigné à un critère.',
            'evaluateurs_criteres.*.critere_id.exists' => 'Le critère sélectionné n\'existe pas.',
            'evaluateurs_criteres.*.evaluateur_id.exists' => 'L\'évaluateur sélectionné n\'existe pas.',
            'evaluateurs_criteres.*.categorie_critere_id.exists' => 'La catégorie de critère sélectionnée n\'existe pas.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'type_evaluation' => 'type d\'évaluation',
            'date_debut_evaluation' => 'date de début',
            'date_fin_evaluation' => 'date de fin',
            'projetable_id' => 'projet',
            'evaluateur_principal_id' => 'évaluateur principal',
        ];
    }
}