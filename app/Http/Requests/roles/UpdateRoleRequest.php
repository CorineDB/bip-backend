<?php

namespace App\Http\Requests\roles;

use App\Models\Dgpd;
use App\Models\Dpaf;
use App\Models\Organisation;
use App\Models\Role;
use App\Models\Permission;
use App\Rules\HashedExistsMultiple;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array($user->type, ['super-admin', 'dgpd', 'dpaf', 'organisation']) || (in_array($user->profilable_type, [Dgpd::class, Dpaf::class, Organisation::class]) && ($user->hasPermission('modifier-un-role') || $user->hasPermission('gerer-les-roles'))));
    }

    protected function prepareForValidation(): void
    {
        $roleId = $this->route('role');

        if ($roleId && is_string($roleId) && !is_numeric($roleId)) {
            $roleId = Role::unhashId($roleId);
            $this->merge(['_role_id' => $roleId]);
        }
    }

    public function rules(): array
    {
        $roleId = $this->input('_role_id') ?? $this->route('role');

        $profilable = auth()->user()->profilable;

        return [
            'nom' => ['required', Rule::unique('roles', 'nom')->ignore($roleId)
            ->when($profilable, function($query) use($profilable) {
                $query->where('roleable_type', get_class($profilable))
                ->where('roleable_id', $profilable->id);
            })->whereNull('deleted_at')],
            'description' => 'nullable|string|max:1000',
            'permissions' => ['sometimes', 'array', 'min:1', new HashedExistsMultiple(Permission::class)],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du rôle est obligatoire.',
            'nom.string' => 'Le nom du rôle doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du rôle ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom de rôle existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.'
        ];
    }
}
