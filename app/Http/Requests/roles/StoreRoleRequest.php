<?php

namespace App\Http\Requests\roles;

use App\Models\Permission;
use App\Rules\HashedExistsMultiple;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $profilable = auth()->user()->profilable;

        return [
            'nom' => ['required', Rule::unique('roles', 'nom')
            ->when($profilable, function($query) use($profilable) {
                $query->where('roleable_type', get_class($profilable))
                ->where('roleable_id', $profilable->id);
            })
            ->whereNull('deleted_at')],

            'description' => 'nullable|string|max:1000',
            'permissions' => ['required', 'array', 'min:1', new HashedExistsMultiple(Permission::class)],
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
