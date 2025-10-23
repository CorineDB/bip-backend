<?php

namespace App\Http\Requests\dgpd;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDgpdRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && (in_array(auth()->user()->type, ['super-admin']) || ($user->hasPermissionTo('creer-la-dgpd') || $user->hasPermissionTo('gerer-la-dgpd')));
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', Rule::unique('dgpd', 'nom')->whereNull('deleted_at')],
            'description' => 'nullable|string',
            "admin" => ["required"],
            'admin.email' => ["required", "email", "max:255", Rule::unique('users', 'email')->whereNull('deleted_at')],

            // Attributs de personne
            'admin.personne.nom' => 'required|string|max:255',
            'admin.personne.prenom' => 'required|string|max:255',
            'admin.personne.poste' => 'nullable|string|max:255'
        ];
    }
}
