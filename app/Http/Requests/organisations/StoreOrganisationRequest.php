<?php

namespace App\Http\Requests\organisations;

use App\Enums\EnumTypeOrganisation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'=> ['required', 'string', Rule::unique('organisations', 'nom')->whereNull('deleted_at')],
            'description' => 'nullable|string',
            'type' => ['required', Rule::in(EnumTypeOrganisation::values())],
            'parentId' => [Rule::requiredIf($this->type != 'ministere'), Rule::exists('organisations', 'id')->whereNull('deleted_at')]
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