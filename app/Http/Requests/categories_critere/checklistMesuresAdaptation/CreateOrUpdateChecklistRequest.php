<?php

namespace App\Http\Requests\categories_critere\checklistMesuresAdaptation;

use App\Models\Secteur;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CreateOrUpdateChecklistRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Structure des critères
            'criteres' => ['required', 'array', 'min:1'],

            'criteres.*.id' => [
                'sometimes',
                Rule::exists('criteres', 'id')
                    ->whereNull('deleted_at')
            ],
            'criteres.*.intitule' => ['required', 'string', 'max:255'],
            'criteres.*.ponderation' => ['required', 'numeric', 'min:0', 'max:100'],
            'criteres.*.commentaire' => ['nullable', 'string'],
            'criteres.*.is_mandatory' => ['boolean'],

            // Structure des secteurs par critère
            'criteres.*.secteurs' => ['required', 'array', 'min:1'],

            'criteres.*.secteurs.*.secteur_id' => [
                'required',
                Rule::exists('secteurs', 'id')
                    //->where('type', 'sous-secteur')
                    ->whereNull('deleted_at')
            ],

            // Structure des mesures par secteur
            'criteres.*.secteurs.*.mesures' => ['required', 'array', 'min:1'],

            'criteres.*.secteurs.*.mesures.*.id' => [
                'sometimes',
                Rule::exists('notations', 'id')
                    ->whereNull('deleted_at')
            ],
            'criteres.*.secteurs.*.mesures.*.libelle' => ['required', 'string', 'max:255'],
            'criteres.*.secteurs.*.mesures.*.valeur' => ['nullable', 'string', 'max:100'],
            'criteres.*.secteurs.*.mesures.*.commentaire' => ['nullable', 'string']
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $this->validateConsistentSecteurs($validator);
            //$this->validateSecteursExistence($validator);
            $this->validateTotalPonderation($validator);
        });
    }

    /**
     * Valider que tous les critères ont exactement les mêmes secteurs (même nombre et mêmes noms)
     */
    private function validateConsistentSecteurs(Validator $validator): void
    {
        $criteres = $this->input('criteres', []);

        if (empty($criteres)) {
            return;
        }

        // Extraire les secteur_id du premier critère comme référence
        $premierCritere = array_values($criteres)[0];
        $secteursReference = collect($premierCritere['secteurs'] ?? [])
            ->pluck('secteur_id')
            ->sort()
            ->values()
            ->toArray();
        $nombreSecteursReference = count($secteursReference);

        // Vérifier qu'il y a au moins un secteur
        if ($nombreSecteursReference === 0) {
            $validator->errors()->add(
                'criteres.0.secteurs',
                'Chaque critère doit avoir au moins un secteur défini.'
            );
            return;
        }

        // Vérifier que tous les autres critères ont exactement les mêmes secteurs
        foreach ($criteres as $critereIndex => $critere) {
            if (!isset($critere['secteurs']) || !is_array($critere['secteurs'])) {
                $validator->errors()->add(
                    "criteres.{$critereIndex}.secteurs",
                    "Le critère doit avoir des secteurs définis."
                );
                continue;
            }

            $secteursCritere = collect($critere['secteurs'])
                ->pluck('secteur_id')
                ->sort()
                ->values()
                ->toArray();
            $nombreSecteursCritere = count($secteursCritere);

            // Vérifier le nombre de secteurs
            if ($nombreSecteursCritere !== $nombreSecteursReference) {
                $validator->errors()->add(
                    "criteres.{$critereIndex}.secteurs",
                    "Tous les critères doivent avoir exactement le même nombre de secteurs. " .
                    "Nombre attendu: {$nombreSecteursReference}, trouvé: {$nombreSecteursCritere}."
                );
                continue;
            }

            // Vérifier que les IDs des secteurs correspondent exactement
            if ($secteursCritere !== $secteursReference) {
                $validator->errors()->add(
                    "criteres.{$critereIndex}.secteurs",
                    "Tous les critères doivent avoir exactement les mêmes secteurs. " .
                    "Secteurs attendus (IDs): " . implode(', ', $secteursReference) . ". " .
                    "Secteurs trouvés (IDs): " . implode(', ', $secteursCritere) . "."
                );
            }

            // Vérification supplémentaire : chaque secteur doit avoir au moins une mesure
            foreach ($critere['secteurs'] as $secteurIndex => $secteurData) {
                if (!isset($secteurData['mesures']) || empty($secteurData['mesures'])) {
                    $secteurId = $secteurData['secteur_id'] ?? 'inconnu';
                    $validator->errors()->add(
                        "criteres.{$critereIndex}.secteurs.{$secteurIndex}.mesures",
                        "Le secteur (ID: {$secteurId}) doit avoir au moins une mesure définie."
                    );
                }
            }
        }
    }

    /**
     * Valider que la somme des pondérations ne dépasse pas 100%
     */
    private function validateTotalPonderation(Validator $validator): void
    {
        $criteres = $this->input('criteres', []);
        $totalPonderation = 0;

        foreach ($criteres as $critere) {
            if (isset($critere['ponderation'])) {
                $totalPonderation += (float) $critere['ponderation'];
            }
        }

        if ($totalPonderation > 100) {
            $validator->errors()->add(
                'criteres',
                "La somme des pondérations ({$totalPonderation}%) ne peut pas dépasser 100%."
            );
        }

        if ($totalPonderation < 100) {
            $validator->errors()->add(
                'criteres',
                "La somme des pondérations ({$totalPonderation}%) devrait idéalement être égale à 100%. " .
                "Pondération manquante: " . (100 - $totalPonderation) . "%."
            );
        }
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Le type de checklist est requis.',
            'criteres.required' => 'Au moins un critère est requis.',
            'criteres.*.intitule.required' => 'L\'intitulé du critère est requis.',
            'criteres.*.ponderation.required' => 'La pondération du critère est requise.',
            'criteres.*.ponderation.min' => 'La pondération doit être positive.',
            'criteres.*.ponderation.max' => 'La pondération ne peut pas dépasser 100%.',
            'criteres.*.secteurs.required' => 'Au moins un secteur est requis par critère.',
            'criteres.*.secteurs.*.mesures.required' => 'Au moins une mesure est requise par secteur.',
            'criteres.*.secteurs.*.mesures.*.libelle.required' => 'Le libellé de la mesure est requis.',
            'documents_referentiel.*.file' => 'Le document de référence doit être un fichier valide.',
            'documents_referentiel.*.mimes' => 'Le document doit être au format PDF, DOC, DOCX ou TXT.',
            'documents_referentiel.*.max' => 'Le document ne peut pas dépasser 10 MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // S'assurer que le slug est généré automatiquement
        $this->merge([
            'slug' => 'checklist-mesures-adaptation-haut-risque'
        ]);
    }

    /**
     * Get the validation data from the request.
     */
    public function validationData(): array
    {
        return $this->all();
    }

    /**
     * Handle a passed validation attempt.
     */
    protected function passedValidation(): void
    {
        // Optionnel: normaliser les données après validation réussie
        $this->normalizeData();
    }

    /**
     * Normaliser les données pour un traitement cohérent
     */
    private function normalizeData(): void
    {
        $criteres = $this->input('criteres', []);

        // Normaliser les pondérations en nombres décimaux
        foreach ($criteres as &$critere) {
            if (isset($critere['ponderation'])) {
                $critere['ponderation'] = (float) $critere['ponderation'];
            }

            // Normaliser les valeurs de notation
            if (isset($critere['secteurs'])) {
                foreach ($critere['secteurs'] as &$secteur) {
                    if (isset($secteur['mesures'])) {
                        foreach ($secteur['mesures'] as &$mesure) {
                            // Nettoyer les espaces
                            $mesure['libelle'] = trim($mesure['libelle']);
                            if(isset($mesure['valeur'])){
                                $mesure['valeur'] = trim($mesure['valeur']);

                            }
                        }
                    }
                }
            }
        }

        // Remettre les données normalisées dans la requête
        $this->merge(['criteres' => $criteres]);
    }
}