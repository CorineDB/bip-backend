<?php

namespace App\Http\Requests\roles;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255|unique:roles,nom',
            'description' => 'nullable|string|max:1000',
            'permissions' => ['array', 'min:1'],
            'permissions.*' => ['required|integer|distinct|exists:permissions,id'],
            'roleable_type' => 'nullable|string|max:255',
            'roleable_id' => 'nullable|integer|min:1'
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