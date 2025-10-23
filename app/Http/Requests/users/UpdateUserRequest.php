<?php

namespace App\Http\Requests\users;

use App\Models\Dgpd;
use App\Models\Dpaf;
use App\Models\Organisation;
use App\Models\Role;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin', 'dgpd', 'dpaf', 'organisation']) || (in_array($user->profilable_type, [Dgpd::class, Dpaf::class, Organisation::class]) && ($user->hasPermission('modifier-un-utilisateur') || $user->hasPermission('gerer-les-utilisateurs'))));
    }

    public function rules(): array
    {
        $userId = $this->route('user') ? ((is_string($this->route('user')) || is_numeric($this->route('user'))) ? $this->route('user') : ($this->route('user')->id)) : $this->route('id');

        $profilable = auth()->user()->profilable;
        $isRequired = $profilable ? (((get_class($profilable) == "App\\Models\\Dpaf") || (get_class($profilable) == "App\\Models\\dgpd")) && !auth()->user()->personne->organismeId) : false;


        return [
            'roleId' => ['nullable', new HashedExists(Role::class, 'id', function($query) use ($profilable) {
                $query->where("roleable_id", $profilable ? $profilable->id : null)
                      ->where("roleable_type", $profilable ? get_class($profilable) : null)
                      ->whereNull('deleted_at');
            })],

            // Attributs de personne
            'personne.nom' => 'required|string|max:255',
            'personne.prenom' => 'required|string|max:255',
            'personne.poste' => 'nullable|string|max:255',
            //'personne.organismeId'=> ["sometimes", Rule::requiredIf($isRequired), new HashedExists(Organisation::class)]
        ];
    }

    public function messages(): array
    {
        return [
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.max' => 'L\'adresse email ne peut pas dépasser 255 caractères.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',

            'roleId.required' => 'L\'ID du rôle est obligatoire.',
            'roleId.integer' => 'L\'ID du rôle doit être un nombre entier.',
            'roleId.exists' => 'Le rôle sélectionné n\'existe pas.',

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
