<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePassportClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('oauth_clients', 'name')->ignore($this->route('id'))
            ],
            'redirect' => 'nullable|string|url|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom du client est obligatoire.',
            'name.string' => 'Le nom du client doit être une chaîne de caractères.',
            'name.max' => 'Le nom du client ne peut pas dépasser 255 caractères.',
            'name.unique' => 'Un client avec ce nom existe déjà.',
            'redirect.url' => 'L\'URL de redirection doit être une URL valide.',
            'redirect.max' => 'L\'URL de redirection ne peut pas dépasser 500 caractères.',
        ];
    }
}