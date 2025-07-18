<?php

namespace App\Http\Requests\odds;

use Illuminate\Foundation\Http\FormRequest;

class StoreOddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'odd' => 'required|string|unique:odds,odd'
        ];
    }

    public function messages(): array
    {
        return [
            'odd.required' => 'L\'ODD est obligatoire.',
            'odd.string' => 'L\'ODD doit être une chaîne de caractères.',
            'odd.unique' => 'Cet ODD existe déjà.'
        ];
    }
}