<?php

namespace App\Http\Requests\groupes_utilisateur;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddUsersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'users' => 'required|array|min:1',
            'users.*' => [
                'integer',
                Rule::exists('users', 'id')->whereNull("roleId")->whereNull('deleted_at')
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'users.required' => 'Au moins un utilisateur doit être spécifié.',
            'users.array' => 'Les utilisateurs doivent être un tableau.',
            'users.min' => 'Au moins un utilisateur doit être fourni.',
            'users.*.integer' => 'Chaque ID d\'utilisateur doit être un nombre entier.',
            'users.*.exists' => 'Un ou plusieurs utilisateurs spécifiés n\'existent pas.',
        ];
    }
}