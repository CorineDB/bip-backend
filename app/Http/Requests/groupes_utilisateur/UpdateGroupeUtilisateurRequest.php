<?php

namespace App\Http\Requests\groupes_utilisateur;

use App\Models\Dgpd;
use App\Models\Dpaf;
use App\Models\Organisation;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupeUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array($user->type, ['super-admin', 'dgpd', 'dpaf', 'organisation']) || (in_array($user->profilable_type, [Dgpd::class, Dpaf::class, Organisation::class]) && ($user->hasPermission('modifier-un-groupe-utilisateur') || $user->hasPermission('gerer-les-groupes-utilisateur'))));
    }

    public function rules(): array
    {
        $groupeId = $this->route('groupe_utilisateur')
            ? ((is_string($this->route('groupe_utilisateur')) || is_numeric($this->route('groupe_utilisateur')))
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
                new HashedExists(Permission::class, 'id', function($query) {
                    $query->whereNull('deleted_at');
                })
            ],

            // Rôles
            'roles' => 'nullable|array|min:1',
            'roles.*' => [
                new HashedExists(Role::class, 'id', function($query) use ($profilable) {
                    if ($profilable) {
                        $query->where('roleable_type', get_class($profilable))
                              ->where('roleable_id', $profilable->id);
                    }
                    $query->whereNull('deleted_at');
                })
            ],

            // Utilisateurs (optionnels)
            'users' => 'nullable|array',
            'users.*.id' => [
                'required_without:users.*.email',
                new HashedExists(User::class, 'id', function($query) {
                    $query->whereNull('deleted_at');
                })
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
