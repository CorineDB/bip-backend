<?php

namespace App\Http\Requests\organisations;

use App\Enums\EnumTypeOrganisation;
use App\Models\Organisation;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrganisationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    protected function prepareForValidation(): void
    {
        $organisationId = $this->route('organisation');

        if ($organisationId && is_string($organisationId) && !is_numeric($organisationId)) {
            $organisationId = Organisation::unhashId($organisationId);
            $this->merge(['_organisation_id' => $organisationId]);
        }
    }

    public function rules(): array
    {
        $organisationId = $this->input('_organisation_id') ?? $this->route('organisation');

        $personneId = Organisation::find($organisationId)?->personnes()->whereHas("user", function ($query) {
            $query->where("type", "organisation");
        })->first()?->id;

        return [
            'nom' => ['required', 'string', 'max:255', Rule::unique('organisations', 'nom')->ignore($organisationId)->whereNull('deleted_at')],

            'description' => 'nullable|string',
            'type' => ['required', Rule::in(EnumTypeOrganisation::values())],
            'parentId' => [Rule::requiredIf($this->type != 'ministere'), new HashedExists(Organisation::class), 'different:' . $organisationId],

            "admin" => ["sometimes", "array", "min:1"],
            'admin.email' => ["sometimes", "email", "max:255", Rule::unique('users', 'email')->ignore($personneId)->whereNull('deleted_at')],

            // Attributs de personne
            'admin.personne.nom' => 'sometimes|string|max:255',
            'admin.personne.prenom' => 'sometimes|string|max:255',
            'admin.personne.poste' => 'nullable|string|max:255'
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l\'organisation est obligatoire.',
            'nom.string' => 'Le nom de l\'organisation doit être une chaîne de caractères.',
            'nom.max' => 'Le nom de l\'organisation ne peut pas dépasser 255 caractères.',
            'nom.unique' => 'Ce nom d\'organisation existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'type.required' => 'Le type d\'organisation est obligatoire.',
            'type.in' => 'Le type d\'organisation sélectionné n\'est pas valide. Les valeurs autorisées sont : ' . implode(', ', EnumTypeOrganisation::values()),
            'parentId.integer' => 'L\'ID de l\'organisation parent doit être un nombre entier.',
            'parentId.exists' => 'L\'organisation parent sélectionnée n\'existe pas.',
            'parentId.different' => 'Une organisation ne peut pas être son propre parent.'
        ];
    }
}
