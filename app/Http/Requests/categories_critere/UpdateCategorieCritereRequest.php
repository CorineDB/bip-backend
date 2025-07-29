<?php

namespace App\Http\Requests\categories_critere;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategorieCritereRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'sometimes|string',
            'slug' => 'sometimes|string',
            'is_mandatory' => 'boolean',

            'notations' => 'sometimes|array|min:1',
            'notations.*.id' => 'nullable|exists:notations,id',
            'notations.*.libelle' => 'required_with:notations|string|max:255',
            'notations.*.valeur' => 'required_with:notations|string|max:255',
            'notations.*.commentaire' => 'nullable|string',
            
            'criteres' => 'sometimes|array|min:1',
            'criteres.*.id' => 'nullable|exists:criteres,id',
            'criteres.*.intitule' => 'required_with:criteres|string',
            'criteres.*.ponderation' => 'required_with:criteres|numeric|min:0',
            'criteres.*.commentaire' => 'nullable|string',
            'criteres.*.is_mandatory' => 'boolean',
            
            'criteres.*.notations' => 'sometimes|array|min:1',
            'criteres.*.notations.*.id' => 'nullable|exists:notations,id',
            'criteres.*.notations.*.libelle' => 'required_with:criteres.*.notations|string|max:255',
            'criteres.*.notations.*.valeur' => 'required_with:criteres.*.notations|string|max:255',
            'criteres.*.notations.*.commentaire' => 'nullable|string'
        ];
    }
}