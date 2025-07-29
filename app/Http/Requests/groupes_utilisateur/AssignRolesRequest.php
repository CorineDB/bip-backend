<?php

namespace App\Http\Requests\groupes_utilisateur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRolesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $profilable = auth()->user()->profilable;
        return [
            'roles' => 'required|array|min:1',
            'roles.*' => [
                'integer',
                Rule::exists('roles', 'id')
                ->where('roleable_type', get_class($profilable))
                ->where('roleable_id', $profilable->id)
                ->whereNull('deleted_at')
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