<?php

namespace App\Http\Requests\categories_document;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategorieDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categorieId = $this->route('categorie_document') ?? $this->route('id');

        return [
            'nom'=> ['sometimes', 'string', Rule::unique('categories_document', 'nom')->ignore($categorieId)->whereNull('deleted_at')],
            'description' => 'sometimes|nullable|string|max:65535',
            'format' => 'sometimes|required|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de la catégorie est obligatoire.',
            'nom.string' => 'Le nom de la catégorie doit être une chaîne de caractères.',
            'nom.max' => 'Le nom de la catégorie ne peut pas dépasser 65535 caractères.',
            'nom.unique' => 'Cette catégorie existe déjà.',
            'slug.required' => 'Le slug est obligatoire.',
            'slug.string' => 'Le slug doit être une chaîne de caractères.',
            'slug.max' => 'Le slug ne peut pas dépasser 255 caractères.',
            'slug.unique' => 'Ce slug existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 65535 caractères.',
            'format.required' => 'Le format est obligatoire.',
            'format.string' => 'Le format doit être une chaîne de caractères.',
            'format.max' => 'Le format ne peut pas dépasser 255 caractères.'
        ];
    }
}