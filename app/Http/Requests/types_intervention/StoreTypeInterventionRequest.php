<?php

namespace App\Http\Requests\types_intervention;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTypeInterventionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_intervention'=> ['required', 'string', 'max:65535', Rule::unique('types_intervention', 'type_intervention')->whereNull('deleted_at')],

            'secteurId' => ['required', Rule::exists('secteurs', 'id')->whereNull('deleted_at')],
        ];
    }

    public function messages(): array
    {
        return [
            'type_intervention.required' => 'Le type d\'intervention est obligatoire.',
            'type_intervention.string' => 'Le type d\'intervention doit être une chaîne de caractères.',
            'type_intervention.max' => 'Le type d\'intervention ne peut pas dépasser 65535 caractères.',
            'type_intervention.unique' => 'Ce type d\'intervention existe déjà.',
            'secteurId.required' => 'Le secteur est obligatoire.',
            'secteurId.integer' => 'L\'identifiant du secteur doit être un nombre entier.',
            'secteurId.exists' => 'Le secteur sélectionné n\'existe pas.'
        ];
    }
}