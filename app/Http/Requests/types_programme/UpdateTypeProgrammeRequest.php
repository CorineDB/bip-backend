<?php

namespace App\Http\Requests\types_programme;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTypeProgrammeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $typeProgrammeId = $this->route('type_programme') ? (is_string($this->route('type_programme')) ? $this->route('type_programme') : ($this->route('type_programme')->id)) : $this->route('id');

        return [
            'type_programme' => 'sometimes|string|unique:types_programme,type_programme,' . $typeProgrammeId,
            'typeId' => 'sometimes|integer|exists:types_programme,id|different:' . $typeProgrammeId
        ];
    }

    public function messages(): array
    {
        return [
            'type_programme.required' => 'Le type de programme est obligatoire.',
            'type_programme.string' => 'Le type de programme doit être une chaîne de caractères.',
            'type_programme.unique' => 'Ce type de programme existe déjà.',
            'typeId.integer' => 'L\'ID du type parent doit être un nombre entier.',
            'typeId.exists' => 'Le type de programme parent sélectionné n\'existe pas.',
            'typeId.different' => 'Un type de programme ne peut pas être son propre parent.'
        ];
    }
}
