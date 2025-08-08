<?php

namespace App\Http\Requests\categories_critere;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'is_mandatory' => 'boolean',

            'notations' => 'sometimes|array|min:1',
            'notations.*.id' => [
                'sometimes',
                Rule::exists('notations', 'id')
                    ->whereNull('deleted_at')
            ],
            'notations.*.libelle' => 'required_with:notations|string|max:255|distinct',
            'notations.*.valeur' => 'required_with:notations|numeric|max:255|distinct',
            'notations.*.commentaire' => 'nullable|string',

            'criteres' => 'required|array|min:1',

            'criteres.*.id' => [
                'sometimes',
                Rule::exists('criteres', 'id')
                    ->whereNull('deleted_at')
            ],

            'criteres.*.intitule' => 'required_with:criteres|string',
            'criteres.*.ponderation' => 'required_with:criteres|numeric|min:0',
            'criteres.*.commentaire' => 'nullable|string',
            'criteres.*.is_mandatory' => 'boolean',

            'criteres.*.notations' => 'sometimes|array|min:1',

            'criteres.*.notations.*.id' => [
                'sometimes',
                Rule::exists('notations', 'id')
                    ->whereNull('deleted_at')
            ],

            'criteres.*.notations.*.libelle' => 'required_with:criteres.*.notations|string|max:255',
            'criteres.*.notations.*.valeur' => 'required_with:criteres.*.notations|numeric|max:255|distinct',
            'criteres.*.notations.*.commentaire' => 'nullable|string'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $criteres = $this->input('criteres', []);
            // Si on a bien des critères
            if (is_array($criteres)) {
                $totalPonderation = 0;

                foreach ($criteres as $critere) {
                    $ponderation = $critere['ponderation'] ?? 0;
                    $totalPonderation += floatval($ponderation);
                }

                if ($totalPonderation !== 100.0) {
                    $validator->errors()->add('criteres', 'La somme des pondérations doit être exactement égale à 100%. Actuellement: ' . $totalPonderation . '%.');
                }
            }
        });
    }
}
