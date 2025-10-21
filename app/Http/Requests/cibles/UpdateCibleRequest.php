<?php

namespace App\Http\Requests\cibles;

use App\Models\Cible;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCibleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $cibleId = $this->route('cible');

        if ($cibleId && is_string($cibleId) && !is_numeric($cibleId)) {
            $cibleId = Cible::unhashId($cibleId);
            $this->merge(['_cible_id' => $cibleId]);
        }
    }

    public function rules(): array
    {
        $cibleId = $this->input('_cible_id') ?? $this->route('cible');

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
