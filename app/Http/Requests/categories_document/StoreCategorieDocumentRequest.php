<?php

namespace App\Http\Requests\categories_document;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategorieDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:65535|unique:categories_document,nom',
            'description' => 'nullable|string|max:65535',
            'format' => 'required|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.string' => 'Le nom de la catégorie doit être une chaîne de caractères.',
            'nom.max' => 'Le nom de la catégorie ne peut pas dépasser 65535 caractères.',
            'nom.unique' => 'Cette catégorie existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 65535 caractères.',
            'format.required' => 'Le format est obligatoire.',
            'format.string' => 'Le format doit être une chaîne de caractères.',
            'format.max' => 'Le format ne peut pas dépasser 255 caractères.'
        ];
    }
}