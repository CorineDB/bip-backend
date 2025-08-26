<?php

namespace App\Http\Requests\groupes_utilisateur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\User;

class UpdateGroupeUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $groupeId = $this->route('groupe_utilisateur')
            ? (is_string($this->route('groupe_utilisateur'))
                ? $this->route('groupe_utilisateur')
                : $this->route('groupe_utilisateur')->id)
            : $this->route('id');

        $profilable = auth()->user()->profilable;

        return [
            // Champs obligatoires du groupe
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groupes_utilisateur', 'nom')
                    ->ignore($groupeId)
                    ->whereNull('deleted_at')
            ],
            'description' => 'nullable|string',

            // Permissions
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => [
                'required',
                'distinct',
                'integer',
                Rule::exists('permissions', 'id')->whereNull('deleted_at')
            ],

            // Rôles
            'roles' => 'nullable|array|min:1',
            'roles.*' => [
                'integer',
                Rule::exists('roles', 'id')
                    ->when($profilable, function ($query) use ($profilable) {
                        $query->where('roleable_type', get_class($profilable))
                              ->where('roleable_id', $profilable->id);
                    })
                    ->whereNull('deleted_at')
            ],

            // Utilisateurs (optionnels)
            'users' => 'nullable|array',
            'users.*.id' => [
                'required_without:users.*.email',
                Rule::exists('users', 'id')->whereNull('deleted_at')
            ],
            'users.*.email' => [
                'required_without:users.*.id',
                'email',
                'max:255',
            ],
            'users.*.personne' => ['required_with:users.*.email', 'array'],
            'users.*.personne.nom' => 'required_with:users.*.email|string|max:255',
            'users.*.personne.prenom' => 'required_with:users.*.email|string|max:255',
            'users.*.personne.poste' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du groupe est obligatoire.',
            'nom.string' => 'Le nom du groupe doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du groupe ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom de groupe existe déjà pour ce profil.',

            'description.string' => 'La description doit être une chaîne de caractères.',

            'roles.array' => 'Les rôles doivent être un tableau.',
            'roles.*.integer' => 'Chaque ID de rôle doit être un nombre entier.',
            'roles.*.exists' => 'Un ou plusieurs rôles spécifiés n\'existent pas.',

            'users.array' => 'Les utilisateurs doivent être un tableau.',
            'users.*.id.exists' => 'Un ou plusieurs utilisateurs spécifiés n\'existent pas.',
            'users.*.email.unique' => 'Cet email est déjà utilisé par un autre utilisateur.',
        ];
    }

    public function withValidator($validator)
    {
        // Validation custom pour l'unicité des emails
        $validator->after(function ($validator) {
            $users = $this->input('users', []);

            foreach ($users as $index => $user) {
                if (!empty($user['email'])) {
                    $userId = $user['id'] ?? null;

                    if (User::where('email', $user['email'])
                        ->where('id', '!=', $userId)
                        ->whereNull('deleted_at')
                        ->exists()
                    ) {
                        $validator->errors()->add(
                            "users.$index.email",
                            "L'email {$user['email']} est déjà utilisé par un autre utilisateur."
                        );
                    }
                }
            }
        });
    }
}
