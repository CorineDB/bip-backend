<?php

namespace App\Http\Requests\tdrs;

use Illuminate\Foundation\Http\FormRequest;

class EvaluerTdrsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->type, ['dgpd', 'admin']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'evaluations_champs' => 'required|array|min:1',
            'evaluations_champs.*.appreciation' => 'required|string|in:passe,retour,non_accepte',
            'evaluations_champs.*.commentaire' => 'required|string|min:10|max:500',
            'commentaire' => 'nullable|string|max:2000',
            'finaliser' => 'required|boolean',
            'action' => 'nullable|string|in:reviser,abandonner'
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
}