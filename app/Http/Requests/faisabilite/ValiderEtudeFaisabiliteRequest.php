<?php

namespace App\Http\Requests\faisabilite;

use App\Models\Champ;
use App\Models\Projet;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ValiderEtudeFaisabiliteRequest extends FormRequest
{
    protected $champs = [];
    protected $projet = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check(); //&& in_array(auth()->user()->type, ['comite_ministeriel', 'dgpd', 'admin']);
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        // Récupérer l'ID du projet depuis la route
        $projetId = $this->route('projetId');
        if (!$projetId) {
            return;
        }

        // Récupérer le projet avec ses relations
        $this->projet = Projet::with('secteur.parent')->findOrFail($projetId);

        // Récupérer le canevas de checklist de suivi de validation de faisabilité
        $canevas = $this->getChecklistSuiviValidation();
        if (!empty($canevas)) {
            // Extraire tous les IDs des champs du canevas
            $champsValides = $this->extractAllFields($canevas);
            $this->champs = collect($champsValides)->pluck('hashed_id')->filter()->toArray();
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $action = $this->input('action', 'sauvegarder');

        return [
            // Action de validation
            'action' => ['required', 'string', Rule::in(['maturite', 'reprendre', 'abandonner', 'sauvegarder'])],

            // Checklist de suivi de validation (obligatoire sauf pour sauvegarder)
            'checklist_suivi_validation' => [
                $action !== 'sauvegarder' ? 'required' : 'nullable',
                'array'
            ],
            'checklist_suivi_validation.*.checkpoint_id' => [
                'required_with:checklist_suivi_validation',
                new HashedExists(Champ::class)
            ],
            'checklist_suivi_validation.*.remarque' => 'required_with:checklist_suivi_validation|string',
            'checklist_suivi_validation.*.explication' => 'nullable|string|max:1000',

            // Synthèse et recommandations (obligatoire si action != sauvegarder)
            /*'synthese_recommandations' => [
                $action !== 'sauvegarder' ? 'required' : 'nullable',
                'string',
                'min:10'
            ],*/

            // Commentaire (optionnel)
            'commentaire' => 'nullable|string|max:1000',

            // Liste de présence (optionnel)
            'liste_presence' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',

            // Informations de financement (conditionnelles)
            'etude_faisabilite' => function ($attribute, $value, $fail) {
                // Validation conditionnelle basée sur info_etude_faisabilite.est_finance
                if (isset($this->projet->info_etude_faisabilite['est_finance']) &&
                    filter_var($this->projet->info_etude_faisabilite['est_finance'], FILTER_VALIDATE_BOOLEAN)) {
                    if (empty($value)) {
                        $fail("Les informations de financement sont requises lorsque le projet est financé.");
                    }
                }
            },
            'etude_faisabilite.date_demande' => [
                'nullable',
                'date_format:Y-m-d'
            ],
            'etude_faisabilite.date_obtention' => [
                'nullable',
                'date_format:Y-m-d',
                'after_or_equal:etude_faisabilite.date_demande'
            ],
            'etude_faisabilite.montant' => [
                'nullable',
                'numeric',
                'min:0'
            ],
            'etude_faisabilite.reference' => [
                'nullable',
                'string',
                'max:100'
            ],

            // Analyse financière (conditionnelle selon est_finance)
            'analyse_financiere' => function ($attribute, $value, $fail) {
                if (isset($this->projet->info_etude_faisabilite['est_finance']) &&
                    filter_var($this->projet->info_etude_faisabilite['est_finance'], FILTER_VALIDATE_BOOLEAN)) {
                    if (empty($value)) {
                        $fail("Les informations d'analyse financière sont requises lorsque le projet est financé.");
                    }
                }
            },
            'analyse_financiere.duree_vie' => 'nullable|integer|min:1',
            'analyse_financiere.taux_actualisation' => 'nullable|numeric|min:0',
            'analyse_financiere.investissement_initial' => 'nullable|numeric|min:0',
            'analyse_financiere.flux_tresorerie' => 'nullable|array',
            'analyse_financiere.flux_tresorerie.*.t' => 'required_with:analyse_financiere.flux_tresorerie|integer|min:1',
            'analyse_financiere.flux_tresorerie.*.CFt' => 'required_with:analyse_financiere.flux_tresorerie|numeric',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateChecklistSuiviValidation($validator);

            // Déhasher les IDs après validation réussie
            if (!$validator->errors()->any()) {
                $this->dehashChecklistSuiviValidation();
            }
        });
    }

    /**
     * Valider la checklist de suivi de validation
     */
    private function validateChecklistSuiviValidation(Validator $validator): void
    {
        $checklistSuivi = $this->input('checklist_suivi_validation');
        if (!$checklistSuivi || !is_array($checklistSuivi)) {
            return;
        }

        $action = $this->input('action', 'sauvegarder');
        $canevasFields = $this->getCanevasFieldsWithConfigs();

        foreach ($checklistSuivi as $index => $evaluation) {
            $checkpointId = $evaluation['checkpoint_id'] ?? null;
            $remarque = $evaluation['remarque'] ?? null;
            $explication = $evaluation['explication'] ?? null;

            // Vérifier que le checkpoint_id existe dans le canevas
            if ($checkpointId && !in_array($checkpointId, $this->champs)) {
                $validator->errors()->add(
                    "checklist_suivi_validation.{$index}.checkpoint_id",
                    "Le champ sélectionné n'appartient pas à la checklist de suivi."
                );
                continue;
            }

            // Récupérer la configuration du champ
            $fieldConfig = $canevasFields[$checkpointId] ?? null;

            // Validation de la remarque selon le canevas
            if ($action !== 'sauvegarder') {
                if (empty($remarque)) {
                    $validator->errors()->add(
                        "checklist_suivi_validation.{$index}.remarque",
                        "La remarque est obligatoire."
                    );
                } else {
                    $this->validateRemarqueValue($validator, $index, $remarque, $fieldConfig, 'checklist_suivi_validation');
                }
            }

            // Déhasher le checkpoint_id après validation
            if ($checkpointId && !is_int($checkpointId)) {
                $checkpointIdDehashed = Champ::unhashId($checkpointId);
                $allData = $this->all();
                $allData['checklist_suivi_validation'][$index]['checkpoint_id'] = $checkpointIdDehashed;
                $this->replace($allData);
            }
        }
    }

    /**
     * Déhasher les IDs de la checklist de suivi de validation
     */
    private function dehashChecklistSuiviValidation(): void
    {
        $checklistSuivi = $this->input('checklist_suivi_validation');
        if (!$checklistSuivi || !is_array($checklistSuivi)) {
            return;
        }

        $allData = $this->all();
        foreach ($checklistSuivi as $index => $evaluation) {
            if (isset($evaluation['checkpoint_id']) && !is_int($evaluation['checkpoint_id'])) {
                $allData['checklist_suivi_validation'][$index]['checkpoint_id'] = Champ::unhashId($evaluation['checkpoint_id']);
            }
        }
        $this->replace($allData);
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            // Messages pour l'action
            'action.required' => 'L\'action est requise.',
            'action.in' => 'L\'action doit être une des valeurs autorisées.',

            // Messages pour la checklist de suivi de validation
            'checklist_suivi_validation.required' => 'La checklist de suivi de validation est obligatoire.',
            'checklist_suivi_validation.array' => 'La checklist de suivi doit être un tableau.',
            'checklist_suivi_validation.*.checkpoint_id.required_with' => 'L\'ID du point de contrôle est obligatoire.',
            'checklist_suivi_validation.*.remarque.required_with' => 'La remarque est obligatoire.',
            'checklist_suivi_validation.*.explication.max' => 'L\'explication ne peut dépasser 1000 caractères.',

            // Messages pour synthèse et recommandations
            //'synthese_recommandations.required' => 'La synthèse et recommandations est obligatoire.',
            //'synthese_recommandations.min' => 'La synthèse et recommandations doit contenir au moins 10 caractères.',

            // Messages pour commentaire
            'commentaire.max' => 'Le commentaire ne peut dépasser 1000 caractères.',

            // Messages pour les informations de financement
            'etude_faisabilite.date_demande.date_format' => 'La date de demande doit être au format AAAA-MM-JJ.',
            'etude_faisabilite.date_obtention.date_format' => 'La date d\'obtention doit être au format AAAA-MM-JJ.',
            'etude_faisabilite.date_obtention.after_or_equal' => 'La date d\'obtention doit être postérieure ou égale à la date de demande.',
            'etude_faisabilite.montant.numeric' => 'Le montant doit être un nombre.',
            'etude_faisabilite.montant.min' => 'Le montant doit être positif.',
            'etude_faisabilite.reference.max' => 'La référence ne peut dépasser 100 caractères.',

            // Messages pour l'analyse financière
            'analyse_financiere.duree_vie.integer' => 'La durée de vie doit être un nombre entier.',
            'analyse_financiere.duree_vie.min' => 'La durée de vie doit être au moins 1 an.',
            'analyse_financiere.taux_actualisation.numeric' => 'Le taux d\'actualisation doit être un nombre.',
            'analyse_financiere.taux_actualisation.min' => 'Le taux d\'actualisation doit être positif.',
            'analyse_financiere.investissement_initial.numeric' => 'L\'investissement initial doit être un nombre.',
            'analyse_financiere.investissement_initial.min' => 'L\'investissement initial doit être positif.',
            'analyse_financiere.flux_tresorerie.array' => 'Les flux de trésorerie doivent être un tableau.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'action' => 'action de validation',
            'commentaire' => 'commentaire',
            'checklist_controle' => 'checklist de contrôle qualité'
        ];
    }

    /**
     * Get the validation actions with their descriptions
     */
    public function getActionDescriptions(): array
    {
        return [
            'maturite' => 'Projet à maturité - Le projet est validé et passe au statut maturité',
            'reprendre' => 'Reprendre l\'étude de faisabilité - Le projet retourne à la soumission du rapport',
            'abandonner' => 'Abandonner le projet - Le projet est définitivement abandonné',
            'sauvegarder' => 'Sauvegarder - Enregistrer sans changer le statut'
        ];
    }

    /**
     * Récupérer le canevas de checklist de suivi de validation de faisabilité
     */
    protected function getChecklistSuiviValidation(): array
    {
        $documentRepository = app(DocumentRepositoryInterface::class);
        $canevas = $documentRepository->getModel()
            ->where('type', 'checklist')
            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-check-liste-suivi-assurance-qualite-rapport-etude-faisabilite'))
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$canevas || !$canevas->all_champs) {
            return [];
        }

        // Convertir la Collection en array
        return $canevas->all_champs->toArray();
    }

    /**
     * Récupérer les champs du canevas avec leurs configurations
     */
    private function getCanevasFieldsWithConfigs(): array
    {
        $canevas = $this->getChecklistSuiviValidation();

        $fieldsWithConfigs = [];
        foreach ($canevas as $field) {
            if (!empty($field['hashed_id'])) {
                $fieldsWithConfigs[$field['hashed_id']] = $field;
            }
        }

        return $fieldsWithConfigs;
    }

    /**
     * Valider la valeur de la remarque selon les options du champ
     */
    private function validateRemarqueValue(Validator $validator, int $index, $remarque, ?array $fieldConfig, string $fieldName = 'checklist_suivi_validation'): void
    {
        if (!$fieldConfig) return;

        $validationsRules = $fieldConfig['meta_options']['validations_rules'] ?? [];
        $validValues = $validationsRules['in'] ?? ['disponible', 'pas-encore-disponibles'];

        if (!in_array($remarque, $validValues)) {
            $validator->errors()->add(
                "{$fieldName}.{$index}.remarque",
                "La remarque doit être une des valeurs autorisées: " . implode(', ', $validValues)
            );
        }
    }

    /**
     * Extraire tous les champs des éléments du canevas
     */
    private function extractAllFields(array $elements): array
    {
        $fields = [];
        foreach ($elements as $el) {
            // Si l'élément a un ID et des meta_options, c'est probablement un champ
            if (!empty($el['id']) && !empty($el['meta_options'])) {
                $fields[] = $el;
            }
            // Si c'est marqué explicitement comme un champ
            elseif (($el['element_type'] ?? null) === 'field') {
                $fields[] = $el;
            }
            // Récursion pour les éléments enfants (sections)
            if (!empty($el['elements']) && is_array($el['elements'])) {
                $fields = array_merge($fields, $this->extractAllFields($el['elements']));
            }
        }
        return $fields;
    }
}
