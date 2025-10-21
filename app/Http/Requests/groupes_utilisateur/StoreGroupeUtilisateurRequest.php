<?php

namespace App\Http\Requests\groupes_utilisateur;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGroupeUtilisateurRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $profilable = auth()->user()->profilable;

        return [
            // Champs obligatoires du groupe
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groupes_utilisateur', 'nom')
                    ->when($profilable, function($query) use($profilable) {
                        $query->where('profilable_type', get_class($profilable))
                              ->where('profilable_id', $profilable->id);
                    })
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
            'roles' => 'nullable|array|min:0',
            'roles.*' => [
                new HashedExists(Role::class, 'id', function($query) use($profilable) {
                    if ($profilable) {
                        $query->where('roleable_type', get_class($profilable))
                              ->where('roleable_id', $profilable->id);
                    }
                    $query->whereNull('deleted_at');
                })
            ],

            // Utilisateurs (au moins 1)
            'users' => 'required|array|min:1',

            // Cas 1 : utilisateur existant
            'users.*.id' => [
                'required_without:users.*.email',
                new HashedExists(User::class, 'id', function($query) {
                    $query->whereNull('deleted_at');
                })
            ],

            // Cas 2 : nouvel utilisateur
            'users.*.email' => [
                'required_without:users.*.id',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ],

            'users.*.personne' => [
                'required_with:users.*.email',
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
            'nom.required' => 'Le nom du groupe est obligatoire.',
            'nom.string' => 'Le nom du groupe doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du groupe ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom de groupe existe déjà pour ce profil.',

            'description.string' => 'La description doit être une chaîne de caractères.',

            'roles.array' => 'Les rôles doivent être un tableau.',
            'roles.*.integer' => 'Chaque ID de rôle doit être un nombre entier.',
            'roles.*.exists' => 'Un ou plusieurs rôles spécifiés n\'existent pas.',

            'users.array' => 'Les utilisateurs doivent être un tableau.',
            'users.*.integer' => 'Chaque ID d\'utilisateur doit être un nombre entier.',
            'users.*.exists' => 'Un ou plusieurs utilisateurs spécifiés n\'existent pas.',
        ];
    }
}
