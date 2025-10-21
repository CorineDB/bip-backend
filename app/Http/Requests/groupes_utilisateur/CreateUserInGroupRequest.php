<?php

namespace App\Http\Requests\groupes_utilisateur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateUserInGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            // Données utilisateur de base
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ],

            // Données de la personne
            'personne' => 'required|array',
            'personne.nom' => 'required|string|max:255',
            'personne.prenom' => 'required|string|max:255',
            'personne.poste' => 'nullable|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'username.string' => 'Le nom d\'utilisateur doit être une chaîne de caractères.',
            'username.max' => 'Le nom d\'utilisateur ne peut pas dépasser 255 caractères.',
            'username.unique' => 'Ce nom d\'utilisateur est déjà utilisé.',

            'change_password_first_login.boolean' => 'L\'option de changement de mot de passe doit être vraie ou fausse.',
            'send_notification_email.boolean' => 'L\'option d\'envoi d\'email doit être vraie ou fausse.',

            'personne.required' => 'Les données de la personne sont obligatoires.',
            'personne.array' => 'Les données de la personne doivent être un objet.',
            'personne.nom.required' => 'Le nom de la personne est obligatoire.',
            'personne.nom.string' => 'Le nom de la personne doit être une chaîne de caractères.',
            'personne.nom.max' => 'Le nom de la personne ne peut pas dépasser 255 caractères.',
            'personne.prenom.required' => 'Le prénom de la personne est obligatoire.',
            'personne.prenom.string' => 'Le prénom de la personne doit être une chaîne de caractères.',
            'personne.prenom.max' => 'Le prénom de la personne ne peut pas dépasser 255 caractères.',
            'personne.poste.string' => 'Le poste doit être une chaîne de caractères.',
            'personne.poste.max' => 'Le poste ne peut pas dépasser 255 caractères.',
            'personne.telephone.string' => 'Le téléphone doit être une chaîne de caractères.',
            'personne.telephone.max' => 'Le téléphone ne peut pas dépasser 20 caractères.',
            'personne.organismeId.integer' => 'L\'ID de l\'organisation doit être un nombre entier.',
            'personne.organismeId.exists' => 'L\'organisation sélectionnée n\'existe pas.',

            'roleId.integer' => 'L\'ID du rôle doit être un nombre entier.',
            'roleId.exists' => 'Le rôle sélectionné n\'existe pas.',
        ];
    }
}
