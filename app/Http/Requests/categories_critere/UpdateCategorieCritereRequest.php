<?php

namespace App\Http\Requests\categories_critere;

use App\Models\Critere;
use App\Models\Notation;
use App\Repositories\CategorieCritereRepository;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategorieCritereRequest extends FormRequest
{
    protected $categorie;

    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        $categorieId = $this->route('categorie_critere') ? (is_string($this->route('categorie_critere')  || is_numeric($this->route('categorie_critere')) ) ? $this->route('categorie_critere') : ($this->route('categorie_critere')->id)) : $this->route('id');

        // Récupérer le projet avec ses relations
        $this->categorie = app(CategorieCritereRepository::class)->getModel()->findByKey($categorieId);
    }

    public function rules(): array
    {
        return [
            'type'=> ['sometimes', 'string', Rule::unique('categories_document', 'type')->ignore($this->categorie->id)->whereNull('deleted_at')],

            'is_mandatory' => 'boolean',

            'notations' => 'sometimes|array|min:1',
            'notations.*.id' => [
                'sometimes',
                new HashedExists(Notation::class)
                /* Rule::exists('notations', 'id')
                    ->whereNull('deleted_at') */
            ],
            'notations.*.libelle' => 'required_with:notations|string|max:255|distinct',
            'notations.*.valeur' => 'required_with:notations|numeric|max:255|distinct',
            'notations.*.commentaire' => 'nullable|string',

            'criteres' => 'required|array|min:1',

            'criteres.*.id' => [
                'sometimes',
                new HashedExists(Critere::class)
                /* Rule::exists('criteres', 'id')
                    ->whereNull('deleted_at') */
            ],

            'criteres.*.intitule' => 'required_with:criteres|string',
            'criteres.*.ponderation' => 'required_with:criteres|numeric|min:0',
            'criteres.*.commentaire' => 'nullable|string',
            'criteres.*.is_mandatory' => 'boolean',

            'criteres.*.notations' => 'sometimes|array|min:1',

            'criteres.*.notations.*.id' => [
                'sometimes',
                new HashedExists(Notation::class)
                /* Rule::exists('notations', 'id')
                    ->whereNull('deleted_at') */
            ],

            'criteres.*.notations.*.libelle' => 'required_with:criteres.*.notations|string|max:255',
            'criteres.*.notations.*.valeur' => 'required_with:criteres.*.notations|numeric|max:255',
            'criteres.*.notations.*.commentaire' => 'nullable|string',

            // Documents référentiels
            'documents_referentiel' => $this->categorie->documentsReferentiel->count() ? 'nullable|array' : 'required|array|min:0',
            'documents_referentiel.*' => 'distinct|file|mimes:pdf,doc,docx,xls,xlsx,png,jpg,txt|mimetypes:application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,image/png,image/jpeg,text/plain|max:10240'
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
