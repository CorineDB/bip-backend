<?php

namespace App\Http\Requests\Cibles;

use Illuminate\Foundation\Http\FormRequest;

class StoreCibleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cible' => 'required|string|unique:cibles,cible'
        ];
    }

    public function messages(): array
    {
        return [
            'cible.required' => 'La cible est obligatoire.',
            'cible.string' => 'La cible doit être une chaîne de caractères.',
            'cible.unique' => 'Cette cible existe déjà.'
        ];
    }
}