<?php

namespace App\Http\Requests\users;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => 'required|string|max:255',
            'provider_user_id' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'status' => ['required', Rule::in(['actif', 'suspendu', 'invité'])],
            'is_email_verified' => 'boolean',
            'email_verified_at' => 'nullable|date',
            'password' => 'required|string|min:8|confirmed',
            'roleId' => 'required|integer|exists:roles,id',
            'last_connection' => 'nullable|date',
            'ip_address' => 'nullable|ip',
            
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
            'provider.required' => 'Le fournisseur est obligatoire.',
            'provider.string' => 'Le fournisseur doit être une chaîne de caractères.',
            'provider.max' => 'Le fournisseur ne peut pas dépasser 255 caractères.',
            'provider_user_id.required' => 'L\'ID utilisateur du fournisseur est obligatoire.',
            'provider_user_id.string' => 'L\'ID utilisateur du fournisseur doit être une chaîne de caractères.',
            'provider_user_id.max' => 'L\'ID utilisateur du fournisseur ne peut pas dépasser 255 caractères.',
            'username.required' => 'Le nom d\'utilisateur est obligatoire.',
            'username.string' => 'Le nom d\'utilisateur doit être une chaîne de caractères.',
            'username.max' => 'Le nom d\'utilisateur ne peut pas dépasser 255 caractères.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'status.required' => 'Le statut est obligatoire.',
            'status.in' => 'Le statut doit être : actif, suspendu ou invité.',
            'is_email_verified.boolean' => 'La vérification email doit être vraie ou fausse.',
            'email_verified_at.date' => 'La date de vérification email doit être une date valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.string' => 'Le mot de passe doit être une chaîne de caractères.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'roleId.required' => 'L\'ID du rôle est obligatoire.',
            'roleId.integer' => 'L\'ID du rôle doit être un nombre entier.',
            'roleId.exists' => 'Le rôle sélectionné n\'existe pas.',
            'last_connection.date' => 'La dernière connexion doit être une date valide.',
            'ip_address.ip' => 'L\'adresse IP doit être valide.',
            
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