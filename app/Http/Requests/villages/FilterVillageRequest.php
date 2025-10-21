<?php

namespace App\Http\Requests\villages;

use App\Models\Arrondissement;
use App\Models\Commune;
use App\Models\Departement;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;

class FilterVillageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'arrondissement_id' => ['nullable', new HashedExists(Arrondissement::class)],
            'commune_id' => ['nullable', new HashedExists(Commune::class)],
            'departement_id' =>  ['nullable', new HashedExists(Departement::class)],
            'per_page' => 'nullable|integer|min:10|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'search.string' => 'Le terme de recherche doit être une chaîne de caractères.',
            'search.max' => 'Le terme de recherche ne peut pas dépasser 255 caractères.',
            'arrondissement_id.integer' => 'L\'ID de l\'arrondissement doit être un nombre entier.',
            'arrondissement_id.exists' => 'L\'arrondissement sélectionné n\'existe pas.',
            'commune_id.integer' => 'L\'ID de la commune doit être un nombre entier.',
            'commune_id.exists' => 'La commune sélectionnée n\'existe pas.',
            'departement_id.integer' => 'L\'ID du département doit être un nombre entier.',
            'departement_id.exists' => 'Le département sélectionné n\'existe pas.',
            'per_page.integer' => 'Le nombre d\'éléments par page doit être un nombre entier.',
            'per_page.min' => 'Le nombre d\'éléments par page doit être au moins 10.',
            'per_page.max' => 'Le nombre d\'éléments par page ne peut pas dépasser 100.',
            'page.integer' => 'Le numéro de page doit être un nombre entier.',
            'page.min' => 'Le numéro de page doit être au moins 1.',
        ];
    }
}
