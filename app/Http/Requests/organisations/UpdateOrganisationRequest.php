<?php

namespace App\Http\Requests\organisations;

use App\Enums\EnumTypeOrganisation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $organisationId = $this->route('organisation') ? (is_string($this->route('organisation')) ? $this->route('organisation') : ($this->route('organisation')->id)) : $this->route('id');

        return [
            'nom' => 'required|string|max:255|unique:organisations,nom,' . $organisationId,
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(EnumTypeOrganisation::values())],
            'parentId' => 'nullable|integer|exists:organisations,id|different:' . $organisationId
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l\'organisation est obligatoire.',
            'nom.string' => 'Le nom de l\'organisation doit être une chaîne de caractères.',
            'nom.max' => 'Le nom de l\'organisation ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom d\'organisation existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'type.required' => 'Le type d\'organisation est obligatoire.',
            'type.in' => 'Le type d\'organisation sélectionné n\'est pas valide. Les valeurs autorisées sont : ' . implode(', ', EnumTypeOrganisation::values()),
            'parentId.integer' => 'L\'ID de l\'organisation parent doit être un nombre entier.',
            'parentId.exists' => 'L\'organisation parent sélectionnée n\'existe pas.',
            'parentId.different' => 'Une organisation ne peut pas être son propre parent.'
        ];
    }
}