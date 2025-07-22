<?php

namespace App\Http\Requests\roles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', Rule::unique('roles', 'nom')->whereNull('deleted_at')],
            'description' => 'nullable|string|max:1000',
            'permissions' => ['required', 'array', 'min:1'],
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
            'description.max' => 'La description ne peut pas dépasser 1000 caractères.'
        ];
    }
}