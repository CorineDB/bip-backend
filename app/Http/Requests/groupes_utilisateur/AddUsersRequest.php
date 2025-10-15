<?php

namespace App\Http\Requests\groupes_utilisateur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Utilisateurs (optionnels à la création)
            'users' => 'required|array|min:1',

            // Cas 1 : utilisateur existant
            'users.*.id' => [
                'required_without:users.*.email',   // obligatoire si pas d'email
                Rule::exists('users', 'id')->whereNull('deleted_at')
            ],

            // Cas 2 : nouvel utilisateur
            'users.*.email' => [
                'required_without:users.*.id',      // obligatoire si pas d'id
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ],

            'users.*.personne' => [
                'required_with:users.*.email',      // requis si on crée un user
                'array'
            ],
            'users.*.personne.nom' => 'required_with:users.*.email|string|max:255',
            'users.*.personne.prenom' => 'required_with:users.*.email|string|max:255',
            'users.*.personne.poste' => 'nullable|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'users.required' => 'Au moins un utilisateur doit être spécifié.',
            'users.array' => 'Les utilisateurs doivent être un tableau.',
            'users.min' => 'Au moins un utilisateur doit être fourni.',
            'users.*.integer' => 'Chaque ID d\'utilisateur doit être un nombre entier.',
            'users.*.exists' => 'Un ou plusieurs utilisateurs spécifiés n\'existent pas.',
        ];
    }
}
