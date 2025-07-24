<?php

namespace App\Http\Requests\idees_projet;

use App\Models\ComposantProgramme;
use App\Models\Document;
use App\Models\Financement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreIdeeProjetRequest extends FormRequest
{

    private ?Document $ficheIdee = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * Liste des attributs valides pour les fiches idées
     */
    private function getValidAttributs(): array
    {
        return [
            "cout_estimatif_projet",
            "sigle",
            "type_projet",
            "parties_prenantes",
            "objectifs_specifiques",
            "resultats_attendus",
            "cout_dollar_americain",
            "cout_euro",
            "cout_dollar_canadien",
            "risques_immediats",
            "sommaire",
            "objectif_general",
            "conclusions",
            "description",
            "constats_majeurs",
            "public_cible",
            "estimation_couts",
            "description_decision",
            "impact_environnement",
            "aspect_organisationnel",
            "description_extrants",
            "caracteristiques",
            "duree",
            "description_projet",
            "origine",
            "situation_desiree",
            "situation_actuelle",
            "contraintes",
            "echeancier",
            "fondement",
            "secteurId",
            "categorieId",
            "titre_projet"
        ];
    }


    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Charger la fiche idée pour la validation dynamique
        $this->ficheIdee = Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'fiche-idee');
        })
            ->where('type', 'formulaire')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function withValidator(Validator $validator): void
    {
        if (!$this->ficheIdee) {
            return;
        }/*

        $modeFinal = $this->input('est_soumise');
        $attributRules = $this->getValidationRulesByAttribut();

        // Si mode final, valider tous les champs pré-connus obligatoires
        if ($modeFinal) {
            $this->validateRequiredPreKnownFields($validator, $attributRules);
            $this->validateSubmissionCompleteness($validator);
        }

        foreach ($this->input('champs', []) as $index => $champInput) {
            $champ = $this->ficheIdee->all_champs->firstWhere('id', $champInput['id'] ?? null);

            if (!$champ) {
                continue;
            }

            $valeurPresente = array_key_exists('valeur', $champInput);
            $attributPresent = array_key_exists($champ->attribut, $champInput);

            // Vérifier si la valeur n'est pas vide (pour validation de qualité)
            $hasNonEmptyValeur = $valeurPresente && !empty($champInput['valeur']) && $champInput['valeur'] !== '';
            $hasNonEmptyAttribut = $attributPresent && !empty($champInput[$champ->attribut]) && $champInput[$champ->attribut] !== '';
            $hasValue = $hasNonEmptyValeur || $hasNonEmptyAttribut;

            // En mode final, forcer la validation des champs required
            // En mode brouillon, valider si une valeur est présente (même vide)
            $shouldValidate = $modeFinal || $valeurPresente || $attributPresent;

            if ($modeFinal && $this->isChampRequired($champ)) {
                $shouldValidate = true;
            }

            if ($shouldValidate) {
                // Validation pour champs.*.valeur
                if ($valeurPresente || ($modeFinal && $this->isChampRequired($champ))) {
                    $champRules = $this->buildValidationRulesForChamp($champ, $modeFinal, $hasNonEmptyValeur);
                    $validator->addRules([
                        "champs.$index.valeur" => $champRules,
                    ]);
                }

                // Validation pour champs.*.{attribut} (données directes par attribut)
                if ($attributPresent && isset($attributRules[$champ->attribut])) {
                    $rules = $attributRules[$champ->attribut];

                    // En mode final, rendre obligatoire si c'est un champ pré-connu requis
                    if ($modeFinal && !in_array('nullable', $rules)) {
                        $rules = $this->ensureRequiredRule($rules);
                    } elseif (!$modeFinal && $hasNonEmptyAttribut) {
                        // En mode brouillon avec valeur, permettre nullable mais valider la qualité
                        $rules = $this->makeRulesNullableButValidateQuality($rules);
                    }

                    $validator->addRules([
                        "champs.$index.{$champ->attribut}" => $rules,
                    ]);
                }
            }
        } */
    }

    /**
     * Valider les champs pré-connus obligatoires en mode final
     */
    private function validateRequiredPreKnownFields(Validator $validator, array $attributRules): void
    {
        $champsInput = $this->input('champs', []);
        $attributsPresents = [];

        // Collecter tous les attributs présents dans les champs
        foreach ($champsInput as $champInput) {
            if (isset($champInput['id'])) {
                $champ = $this->ficheIdee->all_champs->firstWhere('id', $champInput['id']);
                if ($champ && $champ->attribut) {
                    $attributsPresents[] = $champ->attribut;
                }
            }
        }

        // Vérifier que tous les attributs obligatoires sont présents
        foreach ($attributRules as $attribut => $rules) {
            if (!in_array('nullable', $rules) && !in_array($attribut, $attributsPresents)) {
                $validator->addRules([
                    "missing_required_field_{$attribut}" => ['required'],
                ]);

                // Ajouter une erreur personnalisée
                $validator->after(function ($validator) use ($attribut) {
                    $fieldLabel = $this->getFieldLabel($attribut);
                    $validator->errors()->add('champs', "Le champ '{$fieldLabel}' est obligatoire en mode soumission.");
                });
            }
        }
    }

    /**
     * Vérifier si un champ est requis
     */
    private function isChampRequired($champ): bool
    {
        return isset($champ->meta_options['validations_rules']['required'])
            && $champ->meta_options['validations_rules']['required'] === true;
    }

    /**
     * S'assurer qu'une règle required est présente
     */
    private function ensureRequiredRule(array $rules): array
    {
        if (!in_array('required', $rules) && !in_array('nullable', $rules)) {
            array_unshift($rules, 'required');
        }
        return $rules;
    }

    /**
     * Obtenir le label d'un champ pour les messages d'erreur
     */
    private function getFieldLabel(string $attribut): string
    {
        $fieldLabels = [
            'titre_projet' => 'Titre du projet',
            'sigle' => 'Sigle',
            'description' => 'Description',
            'duree' => 'Durée',
            'secteurId' => 'Secteur',
            'ministereId' => 'Ministère',
            'categorieId' => 'Catégorie',
            'objectif_general' => 'Objectif général',
            'situation_actuelle' => 'Situation actuelle',
            'situation_desiree' => 'Situation désirée',
            'contraintes' => 'Contraintes',
            'description_projet' => 'Description du projet',
            'estimation_couts' => 'Estimation des coûts',
        ];

        return $fieldLabels[$attribut] ?? ucfirst(str_replace('_', ' ', $attribut));
    }

    /**
     * Rendre les règles nullable mais valider la qualité si une valeur est présente
     */
    private function makeRulesNullableButValidateQuality(array $rules): array
    {
        // Remplacer 'required' par 'nullable' pour le mode brouillon
        $rules = array_map(function ($rule) {
            if ($rule === 'required') {
                return 'nullable';
            }
            return $rule;
        }, $rules);

        // S'assurer que 'nullable' est en première position
        if (!in_array('nullable', $rules)) {
            array_unshift($rules, 'nullable');
        }

        // Supprimer les doublons
        return array_unique($rules);
    }

    /**
     * Valider la complétude pour la soumission finale
     */
    private function validateSubmissionCompleteness(Validator $validator): void
    {
        $champsInput = $this->input('champs', []);

        // Vérifier que tous les champs requis ont des valeurs
        $champsRequiresVides = [];

        foreach ($champsInput as $index => $champInput) {
            if (!isset($champInput['id'])) {
                continue;
            }

            $champ = $this->ficheIdee->all_champs->firstWhere('id', $champInput['id']);
            if (!$champ) {
                continue;
            }

            $isRequired = $this->isChampRequired($champ);
            if (!$isRequired) {
                continue;
            }

            // Vérifier si le champ a une valeur
            $hasValue = false;

            if (isset($champInput['valeur']) && !empty($champInput['valeur']) && $champInput['valeur'] !== '') {
                $hasValue = true;
            }

            if (isset($champInput[$champ->attribut]) && !empty($champInput[$champ->attribut]) && $champInput[$champ->attribut] !== '') {
                $hasValue = true;
            }

            if (!$hasValue) {
                $champsRequiresVides[] = $champ->label ?? $champ->attribut;
            }
        }

        // Ajouter les erreurs pour les champs vides
        if (!empty($champsRequiresVides)) {
            $validator->after(function ($validator) use ($champsRequiresVides) {
                foreach ($champsRequiresVides as $champLabel) {
                    $validator->errors()->add('champs', "Le champ requis '{$champLabel}' doit être rempli pour soumettre l'idée de projet.");
                }
            });
        }

        // Vérifier les relations obligatoires
        $this->validateRequiredRelations($validator);
    }

    /**
     * Valider les relations obligatoires pour la soumission
     */
    private function validateRequiredRelations(Validator $validator): void
    {
        $champsInput = $this->input('champs', []);
        $relationsRequired = [
            'odds' => 'Objectifs de Développement Durable (ODD)',
            'cibles' => 'Cibles du projet',
            'orientations_strategiques' => 'Orientations stratégiques',
            'objectifs_strategiques' => 'Objectifs stratégiques',
            'resultats_strategiques' => 'Résultats stratégiques',
            'sources_financement' => 'Sources de financement',
            'departements' => 'Zones d\'intervention (départements)'
        ];

        $relationsManquantes = [];

        foreach ($relationsRequired as $relation => $label) {
            $hasRelation = false;

            foreach ($champsInput as $champInput) {
                if (isset($champInput[$relation]) && is_array($champInput[$relation]) && !empty($champInput[$relation])) {
                    $hasRelation = true;
                    break;
                }
            }

            if (!$hasRelation) {
                $relationsManquantes[] = $label;
            }
        }

        if (!empty($relationsManquantes)) {
            $validator->after(function ($validator) use ($relationsManquantes) {
                foreach ($relationsManquantes as $relationLabel) {
                    $validator->errors()->add('champs', "La relation '{$relationLabel}' est obligatoire pour soumettre l'idée de projet.");
                }
            });
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        /*$baseRules = [
            "id" => ["sometimes", Rule::exists('idees_projet', 'id')->whereNull('deleted_at')],
            "est_soumise" => ["required", "boolean:false"],
            "champs" => [
                "required",
                "array",
                "min:1",
                "max:" . ($this->ficheIdee ? $this->ficheIdee->all_champs->count() : 100),
                function ($attribute, $value, $fail) {
                    if ($this->ficheIdee && count($value) > $this->ficheIdee->all_champs->count()) {
                        $fail("Le nombre de champs dépasse la limite autorisée." . count($value) . " - " . $this->ficheIdee->all_champs->count());
                    }
                }
            ],
            "champs.*.id" => [
                "distinct",
                Rule::requiredIf(!array_keys($this->getValidationRulesByAttribut())),
                Rule::exists('champs', 'id')
                    ->whereIn("id", $this->ficheIdee ? $this->ficheIdee->all_champs->pluck("id") : [])
                    ->whereNull('deleted_at')
            ]
        ];

        // Ajouter les règles pour chaque attribut possible
        if ($this->ficheIdee) {
            foreach ($this->ficheIdee->all_champs as $champ) {
                $baseRules["champs.*.{$champ->attribut}"] = []; // Validation dynamique dans withValidator
            }
        }

        return $baseRules;*/

        $rules = [];

        // Récupère dynamiquement les attributs des champs de la fiche
        $champs = $this->ficheIdee->all_champs;

        foreach ($champs as $champ) {
            $attribut = $champ->attribut;

            // Exemples de règles basées sur le type ou la configuration du champ
            $champRules = [];

            // Par exemple : tous les champs sont requis
            if ($champ->is_required) {
                $champRules[] = 'required';
            } else {
                $champRules[] = 'nullable';
            }

            // Appliquer une règle selon le type (exemple simple)
            switch ($champ->type_champ) {
                case 'number':
                    $champRules[] = 'numeric';
                    break;
                case 'textarea':
                case 'text':
                default:
                    $champRules[] = 'string';
                    break;
            }

            // Ajout au tableau de règles
            $rules["champs.$attribut"] = $champRules;
        }

        return $rules;
    }

    /**
     * Construire les règles de validation pour un champ
     */
    private function buildValidationRulesForChamp($champ, bool $modeFinal = false, bool $hasValue = false): array
    {
        $rules = [];

        // Règle required - En mode final, forcer required si le champ est marqué comme requis
        // En mode brouillon, si une valeur est présente, valider sa qualité
        $isRequired = $champ->meta_options['validations_rules']['required'] ?? false;

        if ($isRequired && $modeFinal) {
            // Champ requis en mode final
            $rules[] = 'required';
        } elseif ($hasValue) {
            // Valeur présente en mode brouillon, valider sa qualité mais accepter null/vide
            $rules[] = 'nullable';
        } elseif ($modeFinal) {
            // Mode final mais champ pas forcément requis
            $rules[] = 'nullable';
        } else {
            // Mode brouillon sans valeur
            $rules[] = 'nullable';
        }

        // Règles selon le type de champ - S'appliquent si valeur présente ou en mode final
        if ($hasValue || $modeFinal) {
            switch ($champ->type_champ) {
                case 'email':
                    $rules[] = 'email';
                    break;

                case 'number':
                    $rules[] = 'numeric';
                    // En mode final ou si valeur présente, éviter les valeurs négatives selon config
                    if (isset($champ->meta_options['validations_rules']['min'])) {
                        $rules[] = 'min:' . $champ->meta_options['validations_rules']['min'];
                    }
                    if (isset($champ->meta_options['validations_rules']['max'])) {
                        $rules[] = 'max:' . $champ->meta_options['validations_rules']['max'];
                    }
                    break;

                case 'date':
                    $rules[] = 'date';
                    // Ajouter des contraintes de date si configurées
                    if (isset($champ->meta_options['validations_rules']['after'])) {
                        $rules[] = 'after:' . $champ->meta_options['validations_rules']['after'];
                    }
                    if (isset($champ->meta_options['validations_rules']['before'])) {
                        $rules[] = 'before:' . $champ->meta_options['validations_rules']['before'];
                    }
                    break;

                case 'text':
                case 'textarea':
                    $rules[] = 'string';
                    // Longueur maximale si définie
                    if (isset($champ->meta_options['validations_rules']['max_length'])) {
                        $rules[] = 'max:' . $champ->meta_options['validations_rules']['max_length'];
                    }
                    // Longueur minimale si définie
                    if (isset($champ->meta_options['validations_rules']['min_length'])) {
                        $rules[] = 'min:' . $champ->meta_options['validations_rules']['min_length'];
                    }
                    // En mode final pour champs requis, au moins un caractère
                    if ($modeFinal && $isRequired && !isset($champ->meta_options['validations_rules']['min_length'])) {
                        $rules[] = 'min:1';
                    }
                    break;

                case 'select':
                case 'radio':
                    // Validation contre les options disponibles
                    $options = $champ->meta_options['configs']['options'] ?? [];
                    if (!empty($options)) {
                        $allowedValues = array_column($options, 'value');
                        $rules[] = 'in:' . implode(',', $allowedValues);
                    }
                    break;

                case 'checkbox':
                    if ($modeFinal && $isRequired) {
                        $rules[] = 'accepted'; // Doit être coché en mode final si requis
                    } else {
                        $rules[] = 'boolean';
                    }
                    break;

                case 'file':
                    $rules[] = 'file';
                    // Ajouter des règles de fichier si définies dans meta_options
                    if (isset($champ->meta_options['configs']['allowed_types'])) {
                        $types = implode(',', $champ->meta_options['configs']['allowed_types']);
                        $rules[] = 'mimes:' . $types;
                    }
                    if (isset($champ->meta_options['configs']['max_size'])) {
                        $rules[] = 'max:' . $champ->meta_options['configs']['max_size'];
                    }
                    if (isset($champ->meta_options['configs']['min_size'])) {
                        $rules[] = 'min:' . $champ->meta_options['configs']['min_size'];
                    }
                    break;

                case 'array':
                    $rules[] = 'array';
                    if ($modeFinal && $isRequired) {
                        $rules[] = 'min:1'; // Au moins un élément en mode final
                    }
                    // Validation des éléments du tableau si configurée
                    if (isset($champ->meta_options['validations_rules']['array_elements'])) {
                        $elementRules = $champ->meta_options['validations_rules']['array_elements'];
                        if (is_array($elementRules)) {
                            foreach ($elementRules as $elementRule) {
                                $rules[] = $elementRule;
                            }
                        }
                    }
                    break;

                case 'url':
                    $rules[] = 'url';
                    break;

                case 'json':
                    $rules[] = 'json';
                    break;
            }
        }

        // Règles personnalisées depuis meta_options
        if (isset($champ->meta_options['validations_rules']['custom_rules'])) {
            $customRules = $champ->meta_options['validations_rules']['custom_rules'];
            if (is_array($customRules)) {
                $rules = array_merge($rules, $customRules);
            }
        }

        // En mode final, ajouter des règles strictes supplémentaires pour les champs requis
        // Ou si une valeur est présente en mode brouillon, appliquer des règles de qualité
        if (($modeFinal && $isRequired) || ($hasValue && !$modeFinal)) {
            $rules = $this->addStrictFinalRules($rules, $champ, $modeFinal);
        }

        return $rules;
    }

    /**
     * Ajouter des règles strictes pour le mode final ou de qualité pour les valeurs présentes
     */
    private function addStrictFinalRules(array $rules, $champ, bool $isFinalMode = true): array
    {
        if ($isFinalMode) {
            // Mode final : règles strictes
            // Supprimer 'nullable' si présent en mode final pour les champs requis
            $rules = array_filter($rules, function ($rule) {
                return $rule !== 'nullable';
            });

            // S'assurer que 'required' est en première position
            if (!in_array('required', $rules)) {
                array_unshift($rules, 'required');
            }
        } else {
            // Mode brouillon avec valeur : garder nullable mais ajouter des règles de qualité
            if (!in_array('nullable', $rules)) {
                array_unshift($rules, 'nullable');
            }
        }

        // Règles de qualité selon le type de champ
        switch ($champ->type_champ) {
            case 'text':
            case 'textarea':
                if ($isFinalMode) {
                    // En mode final, éviter les valeurs vides ou espaces uniquement
                    if (!in_array('min:1', $rules)) {
                        $rules[] = 'min:1';
                    }
                } else {
                    // En mode brouillon, valider la longueur si une valeur est fournie
                    $rules[] = function ($attribute, $value, $fail) {
                        if ($value !== null && $value !== '' && strlen(trim($value)) === 0) {
                            $fail("Le champ :attribute ne peut pas contenir uniquement des espaces.");
                        }
                    };
                }
                break;

            case 'select':
            case 'radio':
                if ($isFinalMode) {
                    // Vérifier que la valeur n'est pas vide en mode final
                    $rules[] = function ($attribute, $value, $fail) {
                        if (empty($value) || $value === '' || $value === null) {
                            $fail("Le champ :attribute doit avoir une valeur sélectionnée.");
                        }
                    };
                } else {
                    // En mode brouillon, vérifier que si une valeur est fournie, elle est valide
                    $rules[] = function ($attribute, $value, $fail) {
                        if ($value !== null && $value !== '' && empty(trim($value))) {
                            $fail("Le champ :attribute doit avoir une valeur valide si renseigné.");
                        }
                    };
                }
                break;

            case 'email':
                // Ajouter une vérification de format plus stricte si nécessaire
                if (!$isFinalMode) {
                    $rules[] = function ($attribute, $value, $fail) {
                        if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fail("Le champ :attribute doit être une adresse email valide.");
                        }
                    };
                }
                break;

            case 'number':
                if (!$isFinalMode) {
                    $rules[] = function ($attribute, $value, $fail) {
                        if ($value !== null && $value !== '' && !is_numeric($value)) {
                            $fail("Le champ :attribute doit être un nombre valide.");
                        }
                    };
                }
                break;
        }

        return array_values(array_unique($rules)); // Réindexer et supprimer doublons
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $messages = [];

        if (!$this->ficheIdee) {
            return $messages;
        }

        $tousLesChamps = collect();
        foreach ($this->ficheIdee->sections as $section) {
            $tousLesChamps = $tousLesChamps->merge($section->champs);
        }
        $tousLesChamps = $tousLesChamps->merge($this->ficheIdee->champs);

        // Messages personnalisés basés sur les labels des champs
        foreach ($tousLesChamps as $champ) {
            $attribut = $champ->attribut;
            $label = $champ->label;

            // Messages pour validation par valeur
            $messages["champs.*.valeur.required"] = "Le champ '{$label}' est obligatoire.";
            $messages["champs.*.valeur.email"] = "Le champ '{$label}' doit être un email valide.";
            $messages["champs.*.valeur.numeric"] = "Le champ '{$label}' doit être un nombre.";
            $messages["champs.*.valeur.date"] = "Le champ '{$label}' doit être une date valide.";
            $messages["champs.*.valeur.string"] = "Le champ '{$label}' doit être du texte.";
            $messages["champs.*.valeur.boolean"] = "Le champ '{$label}' doit être vrai ou faux.";
            $messages["champs.*.valeur.file"] = "Le champ '{$label}' doit être un fichier.";
            $messages["champs.*.valeur.in"] = "La valeur sélectionnée pour '{$label}' n'est pas valide.";
            $messages["champs.*.valeur.max"] = "Le champ '{$label}' ne doit pas dépasser :max caractères.";
            $messages["champs.*.valeur.mimes"] = "Le fichier '{$label}' doit être de type: :values.";

            // Messages pour validation par attribut direct
            $messages["champs.*.{$attribut}.required"] = "Le champ '{$label}' est obligatoire.";
            $messages["champs.*.{$attribut}.email"] = "Le champ '{$label}' doit être un email valide.";
            $messages["champs.*.{$attribut}.numeric"] = "Le champ '{$label}' doit être un nombre.";
            $messages["champs.*.{$attribut}.date"] = "Le champ '{$label}' doit être une date valide.";
            $messages["champs.*.{$attribut}.string"] = "Le champ '{$label}' doit être du texte.";
            $messages["champs.*.{$attribut}.boolean"] = "Le champ '{$label}' doit être vrai ou faux.";
            $messages["champs.*.{$attribut}.file"] = "Le champ '{$label}' doit être un fichier.";
            $messages["champs.*.{$attribut}.in"] = "La valeur sélectionnée pour '{$label}' n'est pas valide.";
            $messages["champs.*.{$attribut}.max"] = "Le champ '{$label}' ne doit pas dépasser :max caractères.";
            $messages["champs.*.{$attribut}.mimes"] = "Le fichier '{$label}' doit être de type: :values.";
        }

        return $messages;
    }

    public function attributes(): array
    {
        $attributes = [];

        if (!$this->ficheIdee) {
            return $attributes;
        }

        foreach ($this->ficheIdee->all_champs as $champ) {
            foreach ($this->input('champs', []) as $index => $champInput) {
                if (($champInput['id'] ?? null) == $champ->id) {
                    // Attributs pour la validation par valeur
                    $attributes["champs.$index.valeur"] = $champ->label;

                    // Attributs pour la validation par attribut direct
                    $attributes["champs.$index.{$champ->attribut}"] = $champ->label;
                }
            }
        }

        return $attributes;
    }

    private function getValidationRulesByAttribut(): array
    {
        $isSubmissionMode = $this->input('est_soumis');

        $baseRules = [
            'sigle' => ['required', 'string', 'max:50', Rule::unique('idees_projet', 'sigle')->whereNull('deleted_at')],

            'titre_projet' => ['required', 'string', 'max:255', Rule::unique('idees_projet', 'titre_projet')->whereNull('deleted_at')],

            'duree' => ['required', 'array'],
            'duree.*' => ['required', 'number'],/*
            'duree' => ['required', 'array', 'min:2'],
            'duree.duree' => ['required', 'decimal', 'min:1'],
            'duree.unite_mesure' => ['required', 'string', 'in:an,mois,semaines'],*/
            'description' => ['required', 'string', 'max:65535'],

            'contraintes' => ['required', 'string', 'max:65535'],
            'description_projet' => ['required', 'string', 'max:65535'],
            'description_extrants' => ['required', 'string', 'max:65535'],
            'echeancier' => ['required', 'string', 'max:65535'],
            'caracteristiques_techniques' => ['required', 'string', 'max:65535'],
            'impact_environnement' => ['required', 'string', 'max:65535'],
            'aspect_organisationnel' => ['required', 'string', 'max:65535'],
            'estimation_couts' => ['required', 'string', 'max:65535'],
            'risques_immediats' => ['required', 'string', 'max:65535'],
            'conclusions' => ['required', 'string', 'max:65535'],
            'sommaire' => ['required', 'string', 'max:65535'],
            'constraintes' => ['required', 'string', 'max:65535'],
            'cout_estimatif_projet' => ['required', 'decimal', 'min:0'],
            /*'cout_estimatif_projet' => ['required', 'array', 'min:2'],
                'cout_estimatif_projet.montant' => ['required', 'decimal', 'min:0'],
                'cout_estimatif_projet.devise' => ['required', 'string', 'in:FCFA'],*/

            'cout_dollar_americain' => ['required', 'decimal', 'min:0'],
            'cout_dollar_canadien' => ['required', 'decimal', 'min:0'],
            'cout_euro' => ['required', 'decimal', 'min:0'],
            'situation_actuelle' => ['required', 'string', 'max:65535'],
            'situation_desiree' => ['required', 'string', 'max:65535'],
            'fondement' => ['required', 'string', 'max:65535'],
            'origine' => ['required', 'string', 'max:65535'],
            'objectif_general' => ['required', 'string', 'max:65535'],

            'objectifs_specifiques' => ['required', 'array', 'min:0'],
            'objectifs_specifiques.*' => ['required', 'string', 'max:65535'],

            'resultats_attendus' => ['required', 'array', 'min:0'],
            'resultats_attendus.*' => ['required', 'string', 'max:65535'],

            'constats_majeurs' => ['required', 'string', 'max:65535'],
            'parties_prenantes' => ['required', 'array', 'min:0'],
            'parties_prenantes.*' => ['required', 'string', 'max:65535'],
            'public_cible' => ['required', 'string', 'max:65535'],

            'categorieId' => ['required', Rule::exists('categories_projet', 'id')->whereNull('deleted_at')],
            'secteurId' => ['required', Rule::exists('secteurs', 'id')->where("type", 'sous-secteur')->whereNull('deleted_at')],
            'odds' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'odds.*' => [
                'required',
                Rule::exists('odds', 'id')->whereNull('deleted_at'),
            ],
            'cibles' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'cibles.*' => [
                'required',
                Rule::exists('cibles', 'id')->whereNull('deleted_at'),
            ],
            'departements' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'departements.*' => [
                'required',
                Rule::exists('departements', 'id')->whereNull("deleted_at")
            ],
            'communes.*' => [
                'required',
                Rule::exists('communes', 'id')->whereNull("deleted_at")
            ],
            'arrondissements.*' => [
                'required',
                Rule::exists('arrondissements', 'id')->whereNull("deleted_at")
            ],
            'villages.*' => [
                'required',
                Rule::exists('villages', 'id')->whereNull("deleted_at")
            ],
            'orientations_strategiques' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'orientations_strategiques.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'orientation-strategique-pnd');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Orientation strategique inconnu');
                    }
                }
            ],
            'objectifs_strategiques' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'objectifs_strategiques.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'objectif-strategique-pnd');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Objectif strategique inconnu');
                    }
                }
            ],
            'resultats_strategiques' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'resultats_strategiques.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'resultats-strategique-pnd');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Resultats strategique inconnu');
                    }
                }
            ],

            'sources_financement' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'sources_financement.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = Financement::where('id', $value)->where('type', 'source')
                        ->exists();

                    if (!$exists) {
                        $fail('La source de financement inconnu');
                    }
                }
            ],
            'axes_pag' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'axes_pag.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'axe-pag');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Axe du pag connu');
                    }
                }
            ],
            'actions_pag' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'actions_pag.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'action-pag');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Action du pag connu');
                    }
                }
            ],
            'piliers_pag' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array'],
            'piliers_pag.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'pilier-pag');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Pilier du pag connu');
                    }
                }
            ],
        ];

        return $baseRules;
    }
}
