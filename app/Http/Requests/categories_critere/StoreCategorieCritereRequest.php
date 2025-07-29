<?php

namespace App\Http\Requests\categories_critere;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategorieCritereRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|string',
            'is_mandatory' => 'boolean',

            'notations' => 'sometimes|array|min:1',
            'notations.*.libelle' => 'required_with:notations|string|max:255',
            'notations.*.valeur' => 'required_with:notations|string|max:255',
            'notations.*.commentaire' => 'nullable|string',

            'criteres' => 'required|array|min:1',
            'criteres.*.id' => 'sometimes|exists:criteres,id',
            'criteres.*.intitule' => 'required|string',
            'criteres.*.ponderation' => 'required|numeric|min:0',
            'criteres.*.commentaire' => 'nullable|string',
            'criteres.*.is_mandatory' => 'boolean',

            'criteres.*.notations' => 'sometimes|array|min:1',
            'criteres.*.notations.*.libelle' => 'required_with:criteres.*.notations|string|max:255',
            'criteres.*.notations.*.valeur' => 'required_with:criteres.*.notations|string|max:255',
            'criteres.*.notations.*.commentaire' => 'nullable|string'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $validator->getData();
            
            $hasNotationsCategorie = !empty($data['notations']);
            $hasNotationsCriteres = false;
            
            if (!empty($data['criteres'])) {
                foreach ($data['criteres'] as $critere) {
                    if (!empty($critere['notations'])) {
                        $hasNotationsCriteres = true;
                        break;
                    }
                }
            }
            
            if (!$hasNotationsCategorie && !$hasNotationsCriteres) {
                $validator->errors()->add('notations', 'Au moins des notations au niveau de la catégorie ou des critères doivent être définies.');
            }
        });
    }
}