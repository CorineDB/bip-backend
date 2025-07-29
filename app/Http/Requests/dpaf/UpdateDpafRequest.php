<?php

namespace App\Http\Requests\dpaf;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDpafRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dpafId = $this->route('dpaf') ? (is_string($this->route('dpaf')) ? $this->route('dpaf') : ($this->route('dpaf')->id)) : $this->route('id');

        return [
            'nom' => ['required', 'string', Rule::unique('dpaf', 'nom')->ignore($dpafId)->whereNull('deleted_at')],
            'description' => 'nullable|string',
            "admin" => ["required"],
            // Attributs de personne
            'admin.personne.nom' => 'required|string|max:255',
            'admin.personne.prenom' => 'required|string|max:255',
            'admin.personne.poste' => 'nullable|string|max:255'
        ];
    }
}