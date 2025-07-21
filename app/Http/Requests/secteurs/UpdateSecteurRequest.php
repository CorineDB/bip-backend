<?php

namespace App\Http\Requests\secteurs;

use App\Enums\EnumTypeSecteur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSecteurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $secteurId = $this->route('secteur') ? (is_string($this->route('secteur')) ? $this->route('secteur') : ($this->route('secteur')->id)) : $this->route('id');

        return [
            'nom'=> ['required', 'string', Rule::unique('secteurs', 'nom')->ignore($secteurId)->whereNull('deleted_at')],
            'description' => 'sometimes|nullable|string|max:65535',
            'type' => ['sometimes', 'required', 'string', Rule::in(EnumTypeSecteur::values())],

            'secteurId' => ['sometimes', Rule::exists('secteurs', 'id')->whereNull('deleted_at'), 'different:' . $secteurId],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du secteur est obligatoire.',
            'nom.string' => 'Le nom du secteur doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du secteur ne peut pas dépasser 65535 caractères.',
            'slug.required' => 'Le slug est obligatoire.',
            'slug.string' => 'Le slug doit être une chaîne de caractères.',
            'slug.max' => 'Le slug ne peut pas dépasser 255 caractères.',
            'slug.unique' => 'Ce slug existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 65535 caractères.',
            'type.required' => 'Le type de secteur est obligatoire.',
            'type.string' => 'Le type de secteur doit être une chaîne de caractères.',
            'type.in' => 'Le type de secteur doit être: ' . implode(', ', EnumTypeSecteur::values()),
            'secteurId.integer' => 'L\'identifiant du secteur parent doit être un nombre entier.',
            'secteurId.exists' => 'Le secteur parent sélectionné n\'existe pas.',
            'secteurId.not_in' => 'Un secteur ne peut pas être son propre parent.'
        ];
    }
}