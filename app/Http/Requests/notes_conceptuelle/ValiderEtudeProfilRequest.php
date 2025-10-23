<?php

namespace App\Http\Requests\notes_conceptuelle;

use App\Models\Champ;
use App\Models\Dgpd;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ValiderEtudeProfilRequest extends FormRequest
{
    protected $champs = [];
    protected $canevas = [];

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = auth()->user();
        return auth()->check() && ($user->hasPermissionTo('valider-l-etude-de-profil') && in_array($user->profilable_type, [Dgpd::class]));
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

        // Récupérer le canevas de checklist de suivi de l'étude de profil
        $this->canevas = $canevas = $this->getChecklistSuiviEtudeProfil();
        if (!empty($canevas)) {
            // Extraire tous les IDs des champs du canevas
            $champsValides = $this->extractAllFields($canevas);
            //$this->champs = collect($champsValides)->pluck('id')->filter()->toArray();
            $this->champs = collect($champsValides)->pluck('hashed_id')->filter()->toArray();
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $decision = $this->input('decision');
        $action = $this->input('action', 'submit');
        $requireChecklist = $decision === 'faire_etude_faisabilite_preliminaire';
        $requireFinancialAnalysis = $requireChecklist && $action === 'submit';

        return [
            // Action: submit (soumettre) ou draft (brouillon)
            'decision'              => 'required|string|in:faire_etude_faisabilite_preliminaire,faire_etude_prefaisabilite,reviser_note_conceptuelle,abandonner_projet,sauvegarder',

            'action'                => $requireChecklist ? 'required|string|in:submit,draft' : 'nullable',
            'commentaire'           => 'required_unless:action,draft|string|min:10|max:2000',
            'est_a_haut_risque'     => 'required_unless:action,draft|boolean:false',

            // Checklist de suivi de l'étude de profil (obligatoire si decision = faire_etude_faisabilite_preliminaire)
            'checklist_suivi_rapport_faisabilite_preliminaire' => [
                $requireChecklist ? 'required_unless:action,draft' : 'nullable',
                'array',
                $requireChecklist ? 'min:1' : 'min:0'
            ],
            'checklist_suivi_rapport_faisabilite_preliminaire.*.checkpoint_id' => [
                $requireChecklist ? 'required' : 'nullable',
                $requireChecklist ? new HashedExists(Champ::class) : "null",
                !empty($this->champs) ? "in:" . implode(",", $this->champs) : 'nullable'
            ],

            // Analyse financière (obligatoire si decision = faire_etude_faisabilite_preliminaire et action = submit)
            'analyse_financiere'                            => $requireFinancialAnalysis ? 'required|array' : 'nullable|array',
            'analyse_financiere.duree_vie'                  => $requireChecklist ? 'required_unless:action,draft|numeric|min:1' : 'nullable|numeric|min:1',
            'analyse_financiere.taux_actualisation'         => $requireChecklist ? 'required_unless:action,draft|numeric' : 'nullable|numeric',
            'analyse_financiere.investissement_initial'     => $requireChecklist ? 'required_unless:action,draft|numeric' : 'nullable|numeric',
            'analyse_financiere.flux_tresorerie'            => $requireChecklist ? 'required_unless:action,draft|array|min:1' : 'nullable|array',
            'analyse_financiere.flux_tresorerie.*.t'        => $requireChecklist ? 'required|numeric|min:1' : 'nullable|numeric|min:1',
            'analyse_financiere.flux_tresorerie.*.CFt'      => $requireChecklist ? 'required|numeric' : 'nullable|numeric'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'decision.required' => 'Une décision est obligatoire.',
            'decision.string' => 'La décision doit être du texte.',
            'decision.in' => 'La décision doit être l\'une des valeurs suivantes : Projet à maturité, Faire une étude de pré-faisabilité, Réviser la note conceptuelle, Abandonner le projet, Sauvegarder.',
            'commentaire.required' => 'Un commentaire est obligatoire.',
            'commentaire.string' => 'Le commentaire doit être du texte.',
            'commentaire.min' => 'Le commentaire doit contenir au moins 10 caractères.',
            'commentaire.max' => 'Le commentaire ne peut dépasser 2000 caractères.',

            // Messages pour l'analyse financière
            'analyse_financiere.required' => 'L\'analyse financière est obligatoire pour cette décision.',
            'analyse_financiere.array' => 'L\'analyse financière doit être un tableau.',
            'analyse_financiere.duree_vie.required' => 'La durée de vie du projet est obligatoire.',
            'analyse_financiere.duree_vie.numeric' => 'La durée de vie doit être un nombre.',
            'analyse_financiere.duree_vie.min' => 'La durée de vie doit être au moins 1 an.',
            'analyse_financiere.taux_actualisation.required' => 'Le taux d\'actualisation est obligatoire.',
            'analyse_financiere.taux_actualisation.numeric' => 'Le taux d\'actualisation doit être un nombre.',
            'analyse_financiere.investissement_initial.required' => 'L\'investissement initial est obligatoire.',
            'analyse_financiere.investissement_initial.numeric' => 'L\'investissement initial doit être un nombre.',
            'analyse_financiere.flux_tresorerie.required' => 'Les flux de trésorerie sont obligatoires.',
            'analyse_financiere.flux_tresorerie.array' => 'Les flux de trésorerie doivent être un tableau.',
            'analyse_financiere.flux_tresorerie.min' => 'Au moins un flux de trésorerie est requis.',
            'analyse_financiere.flux_tresorerie.*.t.required' => 'La période (t) est obligatoire pour chaque flux.',
            'analyse_financiere.flux_tresorerie.*.t.numeric' => 'La période (t) doit être un nombre.',
            'analyse_financiere.flux_tresorerie.*.t.min' => 'La période (t) doit être au moins 1.',
            'analyse_financiere.flux_tresorerie.*.CFt.required' => 'Le montant du flux (CFt) est obligatoire.',
            'analyse_financiere.flux_tresorerie.*.CFt.numeric' => 'Le montant du flux (CFt) doit être un nombre.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'decision' => 'décision',
            'commentaire' => 'commentaire',
            'est_a_haut_risque' => 'est un projet a haut risque',
            'est_dur' => 'est un projet de nature dur',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Vérifier que le projet existe et est au bon statut
            $projetId = $this->route('projetId') ?? $this->route('id');

            if ($projetId) {
                try {
                    $projet = app(\App\Repositories\Contracts\ProjetRepositoryInterface::class)->find($projetId);

                    if (!$projet) {
                        $validator->errors()->add('projet', 'Projet non trouvé.');
                        return;
                    }

                    if ($projet->statut->value !== \App\Enums\StatutIdee::VALIDATION_PROFIL->value) {
                        $validator->errors()->add('projet', 'Le projet n\'est pas à l\'étape de validation d\'étude de profil.');
                        return;
                    }

                } catch (\Exception $e) {
                    $validator->errors()->add('projet', 'Erreur lors de la vérification du projet.');
                }
            }

            // Valider la checklist de suivi de l'étude de profil
            $this->validateChecklistSuiviEtudeProfil($validator);
        });
    }

    /**
     * Get the validation messages with decision labels.
     */
    public function getDecisionLabels(): array
    {
        return [
            'projet_a_maturite' => 'Projet à maturité',
            'faire_etude_prefaisabilite' => 'Faire une étude de pré-faisabilité',
            'reviser_note_conceptuelle' => 'Réviser la note conceptuelle',
            'abandonner_projet' => 'Abandonner le projet',
            'sauvegarder' => 'Sauvegarder'
        ];
    }


    /**
     * Valider la checklist de suivi de l'étude de profil (faisabilité préliminaire)
     */
    private function validateChecklistSuiviEtudeProfil(Validator $validator): void
    {
        // Valider seulement si la décision est de faire une étude de faisabilité préliminaire
        $decision = $this->input('decision');
        if ($decision !== 'faire_etude_faisabilite_preliminaire') {
            return;
        }

        $checklistSuivi = $this->input('checklist_suivi_rapport_faisabilite_preliminaire');
        if (!$checklistSuivi || !is_array($checklistSuivi)) {
            return;
        }

        $estSoumise = $this->input('action', 'submit') === 'submit';
        $canevasFields = $this->getCanevasFieldsWithConfigs();

        $champs = collect($this->canevas)->pluck('id')->filter()->toArray();

        foreach ($checklistSuivi as $index => $evaluation) {
            $checkpointId = $evaluation['checkpoint_id'] ?? null;
            $remarque = $evaluation['remarque'] ?? null;
            $explication = $evaluation['explication'] ?? null;

            // Vérifier que le checkpoint_id existe dans le canevas
            //if ($checkpointId && !in_array($checkpointId, $this->champs)) {
            if ($checkpointId && !in_array($checkpointId, $champs)) {
                $validator->errors()->add(
                    "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.checkpoint_id",
                    "Le champ sélectionné n'appartient pas à la checklist de suivi."
                );
                continue;
            }

            // Récupérer la configuration du champ
            $fieldConfig = $canevasFields[$checkpointId] ?? null;

            // Validation de la remarque
            if ($estSoumise) {
                // Pour submit, la remarque est obligatoire et doit être dans les valeurs autorisées
                if (empty($remarque)) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.remarque",
                        "La remarque est obligatoire pour la soumission finale."
                    );
                } else {
                    $this->validateRemarqueValue($validator, $index, $remarque, $fieldConfig);
                }
            } else {
                // Pour draft, la remarque peut être null ou dans les valeurs autorisées
                if ($remarque !== null) {
                    $this->validateRemarqueValue($validator, $index, $remarque, $fieldConfig);
                }
            }

            // Validation de l'explication selon show_explanation
            $this->validateExplication($validator, $index, $explication, $fieldConfig, $estSoumise);
        }

        // Vérifier que tous les champs obligatoires sont présents pour la soumission finale
        if ($estSoumise && !empty($this->champs)) {
            $champsEvalues = collect($checklistSuivi)->pluck('checkpoint_id')->toArray();
            //$champsManquants = array_diff($this->champs, $champsEvalues);
            $champsManquants = array_diff($champs, $champsEvalues);

            if (!empty($champsManquants)) {
                $validator->errors()->add(
                    'checklist_suivi_rapport_faisabilite_preliminaire',
                    'Tous les champs de la checklist doivent être évalués pour la soumission finale. Champs manquants: ' . implode(', ', $champsManquants)
                );
            }
        }
    }



    /**
     * Récupérer le canevas de la checklist de suivi de l'étude de profil depuis la base de données
     */
    protected function getChecklistSuiviEtudeProfil(): array
    {
        $documentRepository = app(DocumentRepositoryInterface::class);
        $canevas = $documentRepository->getCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire();
        /*$canevas = $documentRepository->getModel()
            ->where('type', 'checklist')
            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-check-liste-suivi-controle-qualite-rapport-etude-faisabilite-preliminaire'))
            ->orderBy('created_at', 'desc')
            ->first();*/
        if (!$canevas || !$canevas->all_champs) {
            return [];
        }

        // Convertir la Collection en array
        return $canevas->all_champs->toArray();
    }

    /**
     * Valider la valeur de la remarque selon les options du champ
     */
    private function validateRemarqueValue(Validator $validator, int $index, $remarque, ?array $fieldConfig): void
    {
        if (!$fieldConfig) return;

        $validationsRules = $fieldConfig['meta_options']['validations_rules'] ?? [];
        $validValues = $validationsRules['in'] ?? ['disponible', 'pas-encore-disponibles'];

        if (!in_array($remarque, $validValues)) {
            $validator->errors()->add(
                "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.remarque",
                "La remarque doit être une des valeurs autorisées: " . implode(', ', $validValues)
            );
        }
    }

    /**
     * Valider l'explication selon la configuration show_explanation
     */
    private function validateExplication(Validator $validator, int $index, $explication, ?array $fieldConfig, bool $estSoumise): void
    {
        if (!$fieldConfig) return;

        $showExplanation = $fieldConfig['meta_options']['configs']['show_explanation'] ?? false;
        $maxLength = $fieldConfig['meta_options']['configs']['explanation_max_length'] ?? 1000;

        // Si show_explanation est false, l'explication ne doit pas être requis même en submit
        if (!$showExplanation) {
            // L'explication est optionnel
            if ($explication !== null && strlen($explication) > $maxLength) {
                $validator->errors()->add(
                    "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.explication",
                    "L'explication ne peut pas dépasser {$maxLength} caractères."
                );
            }
            return;
        }

        // Récupérer la remarque pour ce champ
        $remarque = $this->input("checklist_suivi_rapport_faisabilite_preliminaire.{$index}.remarque");
        $remarqueEstPasEncoreDisponible = $remarque === 'pas-encore-disponibles';

        // Si show_explanation est true, valider selon les règles
        if ($estSoumise) {
            // Pour submit, l'explication est obligatoire si show_explanation = true
            if ($showExplanation && $remarqueEstPasEncoreDisponible) {
                if (empty($explication)) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.explication",
                        //"L'explication est obligatoire pour la soumission finale lorsque le champ d'explication est activé."
                        "L'explication est obligatoire lorsque la remarque est 'pas-encore-disponibles' et que le champ d'explication est activé."
                    );
                } elseif (strlen($explication) < 10) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.explication",
                        "L'explication doit contenir au moins 10 caractères."
                    );
                } elseif (strlen($explication) > $maxLength) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.explication",
                        "L'explication ne peut pas dépasser {$maxLength} caractères."
                    );
                }
            }
        } else {
            // Pour draft, l'explication devient obligatoire SEULEMENT si show_explanation = true ET remarque = "pas-encore-disponibles"
            if ($showExplanation && $remarqueEstPasEncoreDisponible) {
                if (empty($explication)) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.explication",
                        "L'explication est obligatoire lorsque la remarque est 'pas-encore-disponibles' et que le champ d'explication est activé."
                    );
                } elseif (strlen($explication) < 10) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.explication",
                        "L'explication doit contenir au moins 10 caractères."
                    );
                } elseif (strlen($explication) > $maxLength) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.explication",
                        "L'explication ne peut pas dépasser {$maxLength} caractères."
                    );
                }
            } else {
                // Pour draft dans tous les autres cas, l'explication est optionnel mais doit respecter les limites si présent
                if ($explication !== null && strlen($explication) > $maxLength) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_faisabilite_preliminaire.{$index}.explication",
                        "L'explication ne peut pas dépasser {$maxLength} caractères."
                    );
                }
            }
        }
    }

    /**
     * Récupérer les champs du canevas avec leurs configurations
     */
    private function getCanevasFieldsWithConfigs(): array
    {
        $canevas = $this->getChecklistSuiviEtudeProfil();

        $fieldsWithConfigs = [];
        foreach ($canevas as $field) {
            if (!empty($field['id'])) {
                $fieldsWithConfigs[$field['id']] = $field;
            }
        }

        return $fieldsWithConfigs;
    }

    /**
     * Extraire tous les champs du canevas (récursif pour gérer les sections)
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
