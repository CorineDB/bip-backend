<?php

namespace App\Http\Requests\roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $roleId = $this->route('role') ? (is_string($this->route('role')) ? $this->route('role') : ($this->route('role')->id)) : $this->route('id');

        return [
            'nom' => ['required', Rule::unique('roles', 'nom')->ignore($roleId)->whereNull('deleted_at')],

            'description' => 'nullable|string|max:1000',
            'permissions' => ['array', 'min:1'],
            'permissions.*' => ['required', 'distinct', Rule::exists('permissions', 'id')->whereNull('deleted_at')],
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
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.',
            'roleable_type.string' => 'Le type roleable doit être une chaîne de caractères.',
            'roleable_type.max' => 'Le type roleable ne peut pas dépasser 255 caractères.',
            'roleable_id.integer' => 'L\'ID roleable doit être un nombre entier.',
            'roleable_id.min' => 'L\'ID roleable doit être supérieur à 0.'
        ];
    }
}
