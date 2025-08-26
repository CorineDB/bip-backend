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
            // Groupe
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
                Rule::exists('permissions', 'id')->whereNull('deleted_at')
            ],

            // Rôles
            'roles' => 'nullable|array|min:1',
            'roles.*' => [
                Rule::exists('roles', 'id')
                    ->when($profilable, function ($query) use ($profilable) {
                        $query->where('roleable_type', get_class($profilable))
                            ->where('roleable_id', $profilable->id);
                    })
                    ->whereNull('deleted_at')
            ],

            // Utilisateurs
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
            'users.*.personne' => 'required_with:users.*.email|array',
            'users.*.personne.nom' => 'required_with:users.*.email|string|max:255',
            'users.*.personne.prenom' => 'required_with:users.*.email|string|max:255',
            'users.*.personne.poste' => 'nullable|string|max:255',
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes('users.*.email', function ($input, $value) {
            return true; // toujours valide pour appliquer la vérification custom
        }, function ($attribute, $value, $fail) {
            // Récupérer l’index du tableau : users.0.email → 0
            preg_match('/users\.(\d+)\.email/', $attribute, $matches);
            $index = $matches[1] ?? null;
            $userId = $this->input("users.$index.id");

            if (User::where('email', $value)
                ->where('id', '!=', $userId)
                ->whereNull('deleted_at')
                ->exists()
            ) {
                $fail("L'email $value est déjà utilisé par un autre utilisateur.");
            }
        });
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

            'permissions.array' => 'Les permissions doivent être un tableau.',
            'permissions.*.integer' => 'Chaque ID de permission doit être un nombre entier.',
            'permissions.*.exists' => 'Une ou plusieurs permissions spécifiées n\'existent pas.',

            'users.array' => 'Les utilisateurs doivent être un tableau.',
            'users.*.id.exists' => 'Un ou plusieurs utilisateurs spécifiés n\'existent pas.',
            'users.*.email.email' => 'L\'email doit être une adresse valide.',
            'users.*.personne.required_with' => 'Les informations de la personne sont requises pour chaque nouvel utilisateur.',
        ];
    }
}
