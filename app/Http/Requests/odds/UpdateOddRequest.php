<?php

namespace App\Http\Requests\odds;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $oddId = $this->route('odd') ? (is_string($this->route('odd')) ? $this->route('odd') : ($this->route('odd')->id)) : $this->route('id');

        return [
            'odd'=> ['required', 'string', Rule::unique('odds', 'odd')->ignore($oddId)->whereNull('deleted_at')]
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