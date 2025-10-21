<?php

namespace App\Http\Requests\groupes_utilisateur;

use App\Models\Permission;
use App\Models\Role;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $profilable = auth()->user()->profilable;
        return [
            'roles' => [Rule::requiredIf(count($this->input("permissions"))), "array", "min:1"],
            'roles.*' => [
                new HashedExists(Role::class, 'id', function($query) use($profilable) {
                    if ($profilable) {
                        $query->where("roleable_type", get_class($profilable))
                              ->where("roleable_id", $profilable->id);
                    }
                    $query->whereNull("deleted_at");
                })
            ],
            "permissions" => [Rule::requiredIf(count($this->input("roles"))), "array", "min:1"],
            "permissions.*" => [
                "required",
                "distinct",
                new HashedExists(Permission::class, 'id', function($query) {
                    $query->whereNull("deleted_at");
                })
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'roles.required' => 'Au moins un rôle doit être spécifié.',
            'roles.array' => 'Les rôles doivent être un tableau.',
            'roles.min' => 'Au moins un rôle doit être fourni.',
            'roles.*.integer' => 'Chaque ID de rôle doit être un nombre entier.',
            'roles.*.exists' => 'Un ou plusieurs rôles spécifiés n\'existent pas.',
        ];
    }
}
