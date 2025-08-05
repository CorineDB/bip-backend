<?php

namespace App\Http\Requests\dpaf;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDpafRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string'],
            'description' => 'nullable|string',
            'id_ministere' => [Rule::exists('organisations', 'id')->where("type", "ministere")->whereNull('deleted_at'), Rule::unique('dpaf', 'id_ministere')->whereNull('deleted_at')],

            "admin" => ["required"],
            'admin.email' => ["required", "email", "max:255", Rule::unique('users', 'email')->whereNull('deleted_at')],

            // Attributs de personne
            'admin.personne.nom' => 'required|string|max:255',
            'admin.personne.prenom' => 'required|string|max:255',
            'admin.personne.poste' => 'nullable|string|max:255'
        ];
    }
}