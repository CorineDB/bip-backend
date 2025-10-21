<?php

namespace App\Http\Requests\dpaf;

use App\Models\Organisation;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDpafRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string'],
            'description' => 'nullable|string',
            'id_ministere' => ['required', new HashedExists(Organisation::class, 'id', function ($query) {
                $query->where('type', 'ministere')->whereNull('deleted_at');
            }), Rule::unique('dpaf', 'id_ministere')->whereNull('deleted_at')],

            "admin" => ["required"],
            'admin.email' => ["required", "email", "max:255", Rule::unique('users', 'email')->whereNull('deleted_at')],

            // Attributs de personne
            'admin.personne.nom' => 'required|string|max:255',
            'admin.personne.prenom' => 'required|string|max:255',
            'admin.personne.poste' => 'nullable|string|max:255'
        ];
    }
}
