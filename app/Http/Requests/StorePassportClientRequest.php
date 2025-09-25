<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePassportClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:oauth_clients,name',
            'redirect' => 'nullable|string|url|max:500',
            'personal_access_client' => 'boolean',
            'password_client' => 'boolean',
            'confidential' => 'boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du client est obligatoire.',
            'name.string' => 'Le nom du client doit être une chaîne de caractères.',
            'name.max' => 'Le nom du client ne peut pas dépasser 255 caractères.',
            'name.unique' => 'Un client avec ce nom existe déjà.',
            'redirect.url' => 'L\'URL de redirection doit être une URL valide.',
            'redirect.max' => 'L\'URL de redirection ne peut pas dépasser 500 caractères.',
            'personal_access_client.boolean' => 'Le champ personal_access_client doit être un booléen.',
            'password_client.boolean' => 'Le champ password_client doit être un booléen.',
            'confidential.boolean' => 'Le champ confidential doit être un booléen.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier que les types de clients ne sont pas conflictuels
            if ($this->boolean('personal_access_client') && $this->boolean('password_client')) {
                $validator->errors()->add(
                    'client_type',
                    'Un client ne peut pas être à la fois un client d\'accès personnel et un client de mot de passe.'
                );
            }

            // Si c'est un client d'accès personnel, l'URL de redirection n'est pas nécessaire
            if ($this->boolean('personal_access_client') && $this->filled('redirect')) {
                $validator->errors()->add(
                    'redirect',
                    'Les clients d\'accès personnel n\'ont pas besoin d\'URL de redirection.'
                );
            }

            // Si c'est un client d'autorisation code, l'URL de redirection est obligatoire
            if (!$this->boolean('personal_access_client') &&
                !$this->boolean('password_client') &&
                !$this->filled('redirect')) {
                $validator->errors()->add(
                    'redirect',
                    'L\'URL de redirection est obligatoire pour les clients d\'autorisation code.'
                );
            }
        });
    }
}