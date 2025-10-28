<?php

namespace App\Http\Requests\IntegrationBip;

use App\Enums\StatutIdee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjetStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'statut' => [
                'required',
                'string',
                Rule::in(StatutIdee::values())
            ],
            'est_ancien' => [
                'sometimes',
                'boolean'
            ],
            'commentaire' => [
                'required_if:est_ancien,true',
                'nullable',
                'string',
                'max:1000'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'statut.required' => 'Le statut est obligatoire.',
            'statut.string' => 'Le statut doit être une chaîne de caractères.',
            'statut.in' => 'Le statut fourni n\'est pas valide.',
            'est_ancien.boolean' => 'Le champ est_ancien doit être un booléen.',
            'commentaire.required_if' => 'Le commentaire est obligatoire pour les projets anciens.',
            'commentaire.string' => 'Le commentaire doit être une chaîne de caractères.',
            'commentaire.max' => 'Le commentaire ne peut pas dépasser 1000 caractères.',
        ];
    }
}
