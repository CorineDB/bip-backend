<?php

namespace App\Http\Requests\types_intervention;

use App\Models\TypeIntervention;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTypeInterventionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $typeInterventionId = $this->route('type_intervention') ? ((is_string($this->route('type_intervention')) || (is_numeric($this->route('type_intervention')))) ? $this->route('type_intervention') : ($this->route('type_intervention')->id)) :  $this->route('id');

        return [
            'type_intervention' => [
                'sometimes',
                'required',
                'string',
                'max:65535',
                Rule::unique('types_intervention', 'type_intervention')->ignore($typeInterventionId)->whereNull('deleted_at')
            ],
            'secteurId' => ['sometimes', new HashedExists(TypeIntervention::class)]
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
