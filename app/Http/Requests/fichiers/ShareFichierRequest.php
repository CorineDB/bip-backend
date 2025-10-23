<?php

namespace App\Http\Requests\fichiers;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShareFichierRequest extends FormRequest
{
    public function authorize(): bool
    {
        //$user = auth()->user();
        return auth()->check() /* && ($user->hasPermissionTo('partager-un-fichier')) */;
    }

    public function rules(): array
    {
        return [
            'user_ids' => ['required', 'array', 'min:1', 'max:50'],
            'user_ids.*' => ['required', Rule::exists('users', 'id')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(['view', 'edit', 'download', 'share', 'delete'])],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_ids.required' => 'Vous devez spécifier au moins un utilisateur.',
            'user_ids.array' => 'Les IDs utilisateurs doivent être fournis sous forme de tableau.',
            'user_ids.min' => 'Vous devez spécifier au moins un utilisateur.',
            'user_ids.max' => 'Vous ne pouvez pas partager avec plus de 50 utilisateurs à la fois.',
            'user_ids.*.required' => 'Chaque ID utilisateur est obligatoire.',
            'user_ids.*.integer' => 'Chaque ID utilisateur doit être un entier.',
            'user_ids.*.exists' => 'Un ou plusieurs utilisateurs spécifiés n\'existent pas.',

            'permissions.array' => 'Les permissions doivent être fournies sous forme de tableau.',
            'permissions.*.string' => 'Chaque permission doit être une chaîne de caractères.',
            'permissions.*.in' => 'Permission invalide. Les permissions autorisées sont: view, edit, download, share, delete.',

            'expires_at.date' => 'La date d\'expiration doit être une date valide.',
            'expires_at.after' => 'La date d\'expiration doit être dans le futur.',

            'message.string' => 'Le message doit être une chaîne de caractères.',
            'message.max' => 'Le message ne doit pas dépasser 500 caractères.',
        ];
    }

    protected function prepareForValidation()
    {
        // Si permissions n'est pas fourni, utiliser 'view' par défaut
        if (!$this->has('permissions') || empty($this->permissions)) {
            $this->merge([
                'permissions' => ['view']
            ]);
        }
    }
}
