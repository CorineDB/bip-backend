<?php

namespace App\Http\Requests\dgpd;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDgpdRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dpgdId = $this->route('dpgd') ? (is_string($this->route('dpgd')) ? $this->route('dpgd') : ($this->route('dpgd')->id)) : $this->route('id');

        return [
            'nom' => ['required', 'string', Rule::unique('dpgd', 'nom')->ignore($dpgdId)->whereNull('deleted_at')],
            'description' => 'nullable|string',
            "admin" => ["required"],
            // Attributs de personne
            'admin.personne.nom' => 'required|string|max:255',
            'admin.personne.prenom' => 'required|string|max:255',
            'admin.personne.poste' => 'nullable|string|max:255'
        ];
    }
}