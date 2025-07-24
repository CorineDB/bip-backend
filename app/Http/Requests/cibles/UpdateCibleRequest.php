<?php

namespace App\Http\Requests\cibles;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'cible'=> ['required', 'string', Rule::unique('cibles', 'cible')->ignore($cibleId)->whereNull('deleted_at')],
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