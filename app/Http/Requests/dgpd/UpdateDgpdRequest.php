<?php

namespace App\Http\Requests\dgpd;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDgpdRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin']) || ($user->hasPermissionTo('modifier-la-dgpd') || $user->hasPermissionTo('gerer-la-dgpd')));
    }

    public function rules(): array
    {
        $dgpdId = $this->route('dgpd') ? ((is_string($this->route('dgpd'))  || is_numeric($this->route('dgpd'))) ? $this->route('dgpd') : ($this->route('dgpd')->id)) : $this->route('id');

        return [
            'nom' => ['required', 'string', Rule::unique('dgpd', 'nom')->ignore($dgpdId)->whereNull('deleted_at')],
            'description' => 'nullable|string',
            "admin" => ["required"],
            // Attributs de personne
            'admin.personne.nom' => 'required|string|max:255',
            'admin.personne.prenom' => 'required|string|max:255',
            'admin.personne.poste' => 'nullable|string|max:255'
        ];
    }
}
