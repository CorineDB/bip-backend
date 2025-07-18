<?php

namespace App\Http\Requests\users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? (is_string($this->route('user')) ? $this->route('user') : ($this->route('user')->id)) : $this->route('id');

        return [
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'roleId' => 'required|integer|exists:roles,id',
            // Attributs de personne
            'personne.nom' => 'required|string|max:255',
            'personne.prenom' => 'required|string|max:255',
            'personne.poste' => 'nullable|string|max:255',
            'personne.organismeId' => 'required|integer|exists:organisations,id'
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'roleId.required' => 'L\'ID du rôle est obligatoire.',
            'roleId.integer' => 'L\'ID du rôle doit être un nombre entier.',
            'roleId.exists' => 'Le rôle sélectionné n\'existe pas.',

            // Messages pour les attributs de personne
            'personne.nom.required' => 'Le nom de la personne est obligatoire.',
            'personne.nom.string' => 'Le nom de la personne doit être une chaîne de caractères.',
            'personne.nom.max' => 'Le nom de la personne ne peut pas dépasser 255 caractères.',
            'personne.prenom.required' => 'Le prénom de la personne est obligatoire.',
            'personne.prenom.string' => 'Le prénom de la personne doit être une chaîne de caractères.',
            'personne.prenom.max' => 'Le prénom de la personne ne peut pas dépasser 255 caractères.',
            'personne.poste.string' => 'Le poste doit être une chaîne de caractères.',
            'personne.poste.max' => 'Le poste ne peut pas dépasser 255 caractères.',
            'personne.organismeId.required' => 'L\'organisation est obligatoire.',
            'personne.organismeId.integer' => 'L\'ID de l\'organisation doit être un nombre entier.',
            'personne.organismeId.exists' => 'L\'organisation sélectionnée n\'existe pas.'
        ];
    }
}