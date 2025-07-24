<?php

namespace App\Http\Requests\cibles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCibleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cible'=> ['required', 'string', Rule::unique('cibles', 'cible')->whereNull('deleted_at')],
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