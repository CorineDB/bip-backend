<?php

namespace App\Http\Requests\faisabilite;

use Illuminate\Foundation\Http\FormRequest;

class EvaluerTdrsFaisabiliteRequest extends FormRequest
{
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
            'evaluations_champs' => 'required|array|min:1',
            'evaluations_champs.*.champ_id' => 'required|integer|exists:champs,id',
            'evaluations_champs.*.appreciation' => 'required|string|in:passe,retour,non_accepte',
            'evaluations_champs.*.commentaire' => 'nullable|string|max:1000',
            'commentaire' => 'nullable|string|max:2000',
            'action' => 'nullable|string|in:abandonner'
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
}