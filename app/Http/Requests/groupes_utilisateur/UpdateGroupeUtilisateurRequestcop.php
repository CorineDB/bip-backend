<?php

namespace App\Http\Requests\groupes_utilisateur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGroupeUtilisateurRequestcop extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $groupeId = $this->route('groupe_utilisateur') ?
            (is_string($this->route('groupe_utilisateur')) ? $this->route('groupe_utilisateur') : $this->route('groupe_utilisateur')->id) :
            $this->route('id');

        $profilable = auth()->user()->profilable;

        return [
            // Champs obligatoires du groupe
            'nom' => [
                'required',
                'string',
                'max:255',
                Rule::unique('groupes_utilisateur', 'nom')
                    ->ignore($groupeId)
                    ->when($profilable, function ($query) use ($profilable) {
                        $query->where('profilable_type', get_class($profilable))
                            ->where('profilable_id', $profilable->id);
                    })
                    ->whereNull('deleted_at')
            ],
            'description' => 'nullable|string',
            'permissions' => ['required', 'array', 'min:1'],
            'permissions.*' => ['required', 'distinct', Rule::exists('permissions', 'id')->whereNull('deleted_at')],

            // Rôles (optionnels à la création)
            'roles' => 'nullable|array|min:1',
            'roles.*' => [
                'required',
                Rule::exists('roles', 'id')
                    ->when($profilable, function ($query) use ($profilable) {
                        $query->where('roleable_type', get_class($profilable))
                            ->where('roleable_id', $profilable->id);
                    })
                    ->whereNull('deleted_at')
            ],

            // Utilisateurs (optionnels à la création)
            'users' => 'nullable|array',
            'users.*.id' => [
                Rule::exists('users', 'id')/* ->whereNull("roleId")->whereNull("roleId") */->whereNull('deleted_at')
            ],
            // Données utilisateur de base
            'users.*.email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->whereNull('deleted_at')
            ],

            // Données de la personne
            'users.*.personne' => 'required|array',
            'users.*.personne.nom' => 'required|string|max:255',
            'users.*.personne.prenom' => 'required|string|max:255',
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
