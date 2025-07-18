<?php

namespace App\Http\Requests\Cibles;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCibleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $cibleId = $this->route('cible') ? (is_string($this->route('cible')) ? $this->route('cible') : ($this->route('cible')->id)) : $this->route('id');

        return [
            'cible' => 'required|string|unique:cibles,cible,' . $cibleId
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