<?php

namespace App\Http\Requests\documents\canevas_redaction_note_conceptuelle;

use App\Models\Champ;
use App\Models\ChampSection;
use App\Models\Document;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;

class CreateOrUpdateCanevasAppreciationNoteConceptuelleRequest extends FormRequest
{
    private $canevas_appreciation_tdr_faisabilite = null;

    public function authorize(): bool
    {
        return auth()->check() && in_array(auth()->user()->type, ['super-admin', 'dgpd']);
    }

    public function prepareForValidation()
    {
        $this->canevas_appreciation_tdr_faisabilite = Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-appreciation-note-conceptuelle');
        })
        ->where('type', 'checklist')
        ->orderBy('created_at', 'desc')
        ->first();
    }

    /**
     * Valide que les clés sont uniques dans tout le formulaire
     */
    private function validateUniqueKeys($validator)
    {
        $keys = [];

        foreach ($this->input('forms', []) as $index => $element) {
            $this->collectKeysRecursively($element, $keys, $validator, "forms.{$index}");
        }
    }

    /**
     * Collecte les clés récursivement et vérifie leur unicité
     */
    private function collectKeysRecursively($element, &$keys, $validator, $currentPath)
    {
        $keyField = null;
        $elementType = $element['element_type'] ?? 'inconnu';

        // Pour les fields, on utilise 'attribut', pour les sections 'key'
        if ($elementType === 'field' && isset($element['attribut'])) {
            $keyField = $element['attribut'];
        } elseif ($elementType === 'section' && isset($element['key'])) {
            $keyField = $element['key'];
        }

        if ($keyField) {
            if (in_array($keyField, $keys)) {
                if ($elementType === 'field') {
                    $validator->errors()->add("{$currentPath}.attribut", "L'identifiant du champ est duplique '{$keyField}'.");
                } elseif ($elementType === 'section') {
                    $validator->errors()->add("{$currentPath}.key", "L'identifiant de la section est duplique '{$keyField}'.");
                }
            } else {
                $keys[] = $keyField;
            }
        }

        // Traiter récursivement tous les éléments enfants via elements
        if ($element['element_type'] === 'section' && isset($element['elements'])) {
            foreach ($element['elements'] as $subIndex => $subElement) {
                $this->collectKeysRecursively($subElement, $keys, $validator, "{$currentPath}.elements.{$subIndex}");
            }
        }
    }

    /**
     * Valide que les ordres d'affichage sont uniques par niveau
     */
    private function validateOrderPerLevel($validator)
    {
        // Niveau racine
        $this->validateOrdersAtLevel($this->input('forms', []), 'forms', $validator);

        // Traiter récursivement tous les niveaux
        foreach ($this->input('forms', []) as $index => $element) {
            $this->validateOrdersRecursively($element, "forms.{$index}", $validator);
        }
    }

    /**
     * Valide les ordres d'affichage pour un niveau donné
     */
    private function validateOrdersAtLevel($elements, $path, $validator)
    {
        $orders = [];
        foreach ($elements as $index => $element) {
            $order = $element['ordre_affichage'] ?? null;
            if ($order && in_array($order, $orders)) {
                $validator->errors()->add("{$path}.{$index}.ordre_affichage",
                    "L'ordre d'affichage {$order} est déjà utilisé à ce niveau.");
            } else if ($order) {
                $orders[] = $order;
            }
        }
    }

    /**
     * Valide récursivement les ordres d'affichage dans les sections imbriquées
     */
    private function validateOrdersRecursively($element, $currentPath, $validator)
    {
        // Valider les éléments dans elements
        if ($element['element_type'] === 'section' && isset($element['elements'])) {
            $elementsPath = "{$currentPath}.elements";

            // Valider les ordres au niveau actuel
            $this->validateOrdersAtLevel($element['elements'], $elementsPath, $validator);

            // Continuer récursivement
            foreach ($element['elements'] as $subIndex => $subElement) {
                $this->validateOrdersRecursively($subElement, "{$elementsPath}.{$subIndex}", $validator);
            }
        }
    }


    public function rules(): array
    {
        return [
            // Document fields
            'nom' => [
                'required', 'bail',
                function ($attribute, $value, $fail) {
                    $exists = Document::where('nom', $value)
                        ->whereHas('categorie', function ($query) {
                            $query->where('slug', 'canevas-appreciation-note-conceptuelle');
                        })->when($this->canevas_appreciation_tdr_faisabilite, function($query){
                            $query->where("id","<>", $this->canevas_appreciation_tdr_faisabilite->id);
                        })->exists();

                    if ($exists) {
                        $fail('The nom has already been taken for documents in this category.');
                    }
                }
            ],
            'description' => 'nullable|string|max:65535',
            'guide_notation'                  => 'required|array|min:2',
            'guide_notation.*.libelle'        => 'required|string|max:255',
            'guide_notation.*.appreciation'   => 'required|string|max:255',
            'guide_notation.*.description'    => 'nullable|string|max:1000',
            'accept_text'                       => 'required|string|min:10',

            // Validation de evaluation_configs - Structure dynamique des règles d'évaluation
            'evaluation_configs'                        => 'nullable|array',

            // Section results - Résultats possibles de l'évaluation
            'evaluation_configs.results'                => 'nullable|array',
            'evaluation_configs.results.*.value'        => 'required_with:evaluation_configs.results|string|max:255',
            'evaluation_configs.results.*.label'        => 'required_with:evaluation_configs.results|string|max:255',
            'evaluation_configs.results.*.statut_suivant' => 'nullable|string|max:255',
            'evaluation_configs.results.*.message'      => 'nullable|string|max:1000',

            // Section rules - Règles de décision dynamiques
            'evaluation_configs.rules'                  => 'nullable|array',
            'evaluation_configs.rules.reference'        => 'nullable|string|max:500',
            'evaluation_configs.rules.decision_algorithm' => 'nullable|string|max:100',
            'evaluation_configs.rules.evaluation_required_fields' => 'nullable|array',

            // Conditions de décision
            'evaluation_configs.rules.conditions'       => 'nullable|array',
            'evaluation_configs.rules.conditions.*.priority' => 'required_with:evaluation_configs.rules.conditions|integer|min:1',
            'evaluation_configs.rules.conditions.*.name' => 'required_with:evaluation_configs.rules.conditions|string|max:255',
            'evaluation_configs.rules.conditions.*.appreciations_concernees' => 'nullable|array', //required_with:evaluation_configs.rules.conditions
            'evaluation_configs.rules.conditions.*.condition' => 'required_with:evaluation_configs.rules.conditions|array',
            'evaluation_configs.rules.conditions.*.condition.type' => 'required_with:evaluation_configs.rules.conditions.*.condition|string|in:comparison,and,or,default',
            'evaluation_configs.rules.conditions.*.result' => 'required_with:evaluation_configs.rules.conditions|string|max:255',
            'evaluation_configs.rules.conditions.*.message' => 'nullable|string|max:1000',
            'evaluation_configs.rules.conditions.*.recommandations' => 'nullable|array',

            // Forms array - structure flexible avec validation récursive
            'forms' => 'required|array|min:1',
            'forms.*' => 'required|array',
            //'forms.*.startWithNewLine' => 'required|in:false,true'
        ];
    }

    /**
     * Configure la validation après les règles de base
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->has('forms')) {
                $validator->errors()->add('forms', 'Le tableau forms est obligatoire.');
                return;
            }

            // Valider la structure récursivement
            $this->validateFormsStructure($this->input('forms', []), 'forms', $validator);

            // Validation de l'unicité des clés
            $this->validateUniqueKeys($validator);

            // Validation de l'ordre d'affichage unique par niveau
            $this->validateOrderPerLevel($validator);

            // Validation de la cohérence entre guide_notation et regles_decision
            $this->validateEvaluationConfigsCoherence($validator);
        });
    }

    /**
     * Valide la cohérence de la configuration evaluation_configs
     */
    private function validateEvaluationConfigsCoherence($validator)
    {
        // Récupérer les valeurs d'appreciation du guide_notation
        $guideNotation = $this->input('guide_notation', []);
        $validAppreciations = collect($guideNotation)->pluck('appreciation')->filter()->toArray();

        // Récupérer les résultats possibles
        $validResults = collect($this->input('evaluation_configs.results', []))->pluck('value')->filter()->toArray();

        // Valider les conditions de décision
        $conditions = $this->input('evaluation_configs.rules.conditions', []);
        if (!empty($conditions)) {
            foreach ($conditions as $index => $condition) {
                // Vérifier que appreciations_concernees référence des appréciations valides
                $appreciationsConcernees = $condition['appreciations_concernees'] ?? [];
                foreach ($appreciationsConcernees as $appreciation) {
                    if (!in_array($appreciation, $validAppreciations)) {
                        $validator->errors()->add(
                            "evaluation_configs.rules.conditions.{$index}.appreciations_concernees",
                            "L'appréciation '{$appreciation}' n'est pas définie dans guide_notation. " .
                            "Appréciations valides: " . implode(', ', $validAppreciations)
                        );
                    }
                }

                // Vérifier que le result correspond à un résultat valide (si results est défini)
                if (!empty($validResults)) {
                    $result = $condition['result'] ?? null;
                    if ($result && !in_array($result, $validResults)) {
                        $validator->errors()->add(
                            "evaluation_configs.rules.conditions.{$index}.result",
                            "Le résultat '{$result}' n'est pas défini dans evaluation_configs.results. " .
                            "Résultats valides: " . implode(', ', $validResults)
                        );
                    }
                }

                // Vérifier que les conditions qui ne sont pas 'default' ont bien une structure valide
                if (isset($condition['condition']['type']) && $condition['condition']['type'] !== 'default') {
                    $this->validateConditionStructure($condition['condition'], "evaluation_configs.rules.conditions.{$index}.condition", $validator);
                }
            }

            // Vérifier qu'il y a au moins une condition 'default' avec la priorité la plus basse
            $hasDefault = collect($conditions)->contains(function($cond) {
                return ($cond['condition']['type'] ?? '') === 'default';
            });

            if (!$hasDefault) {
                $validator->errors()->add(
                    'evaluation_configs.rules.conditions',
                    'Au moins une condition avec type "default" est requise comme condition par défaut.'
                );
            }
        }
    }

    /**
     * Valide la structure d'une condition (comparison, and, or)
     */
    private function validateConditionStructure($condition, $path, $validator)
    {
        $type = $condition['type'] ?? null;

        if ($type === 'comparison') {
            // Vérifier les champs requis pour une comparaison
            if (empty($condition['field'])) {
                $validator->errors()->add("{$path}.field", "Le champ 'field' est obligatoire pour une condition de type 'comparison'.");
            }
            if (empty($condition['operator'])) {
                $validator->errors()->add("{$path}.operator", "Le champ 'operator' est obligatoire pour une condition de type 'comparison'.");
            }
            if (!isset($condition['value']) && !isset($condition['value_field'])) {
                $validator->errors()->add("{$path}", "Soit 'value' soit 'value_field' doit être défini pour une condition de type 'comparison'.");
            }
            // Valider l'opérateur
            $validOperators = ['>', '>=', '<', '<=', '==', '!='];
            if (isset($condition['operator']) && !in_array($condition['operator'], $validOperators)) {
                $validator->errors()->add("{$path}.operator", "L'opérateur '{$condition['operator']}' n'est pas valide. Opérateurs valides: " . implode(', ', $validOperators));
            }
        } elseif ($type === 'and' || $type === 'or') {
            // Vérifier que 'conditions' est un tableau non vide
            if (empty($condition['conditions']) || !is_array($condition['conditions'])) {
                $validator->errors()->add("{$path}.conditions", "Le champ 'conditions' doit être un tableau non vide pour une condition de type '{$type}'.");
            } else {
                // Valider récursivement les sous-conditions
                foreach ($condition['conditions'] as $subIndex => $subCondition) {
                    $this->validateConditionStructure($subCondition, "{$path}.conditions.{$subIndex}", $validator);
                }
            }
        }
    }

    /**
     * Valide la structure des formulaires de manière récursive
     */
    private function validateFormsStructure($elements, $path, $validator)
    {
        foreach ($elements as $index => $element) {
            $currentPath = "{$path}.{$index}";

            // Validation du type d'élément
            if (!isset($element['element_type']) || !in_array($element['element_type'], ['field', 'section'])) {
                $validator->errors()->add("{$currentPath}.element_type",
                    'Le type d\'élément doit être "field" ou "section".');
                continue;
            }

            // Validation de l'ID hashé si présent (pour les mises à jour)
            if (isset($element['id'])) {
                $idValidator = null;

                if ($element['element_type'] === 'field') {
                    $idValidator = new HashedExists(Champ::class);
                } elseif ($element['element_type'] === 'section') {
                    $idValidator = new HashedExists(ChampSection::class);
                }

                if ($idValidator && !$idValidator->passes("{$currentPath}.id", $element['id'])) {
                    $validator->errors()->add("{$currentPath}.id", $idValidator->message());
                }
            }

            // Validation de l'ordre d'affichage
            if (!isset($element['ordre_affichage']) || !is_integer($element['ordre_affichage']) || $element['ordre_affichage'] < 1) {
                $validator->errors()->add("{$currentPath}.ordre_affichage",
                    'L\'ordre d\'affichage est obligatoire et doit être un entier positif.');
            }

            // Validation de la clé selon le type d'élément
            if ($element['element_type'] === 'field') {
                // Pour les fields, on utilise 'attribut'
                if (isset($element['attribut']) && (!is_string($element['attribut']) || strlen($element['attribut']) > 255)) {
                    $validator->errors()->add("{$currentPath}.attribut",
                        'L\'attribut doit être une chaîne de caractères et ne doit pas dépasser 255 caractères.');
                }
            } elseif ($element['element_type'] === 'section') {
                // Pour les sections, on utilise 'key'
                if (isset($element['key']) && (!is_string($element['key']) || strlen($element['key']) > 255)) {
                    $validator->errors()->add("{$currentPath}.key",
                        'La clé doit être une chaîne de caractères et ne doit pas dépasser 255 caractères.');
                }
            }

            // Validation spécifique aux champs
            if ($element['element_type'] === 'field') {
                $this->validateFieldElement($element, $currentPath, $validator);
            }

            // Validation spécifique aux sections
            if ($element['element_type'] === 'section') {
                $this->validateSectionElement($element, $currentPath, $validator);

                // Validation récursive des éléments enfants via elements
                if (isset($element['elements']) && is_array($element['elements'])) {
                    $this->validateFormsStructure($element['elements'], "{$currentPath}.elements", $validator);
                }
            }
        }
    }

    /**
     * Valide un élément de type field
     */
    private function validateFieldElement($element, $path, $validator)
    {
        // Label obligatoire
        if (!isset($element['label']) || !is_string($element['label']) || strlen($element['label']) > 500) {
            $validator->errors()->add("{$path}.label",
                'Le libellé du champ est obligatoire et ne doit pas dépasser 500 caractères.');
        }

        // L'attribut est déjà validé dans validateFormsStructure

        // Type de champ obligatoire et doit être 'radio'
        if (!isset($element['type_champ']) || !is_string($element['type_champ'])) {
            $validator->errors()->add("{$path}.type_champ",
                'Le type de champ est obligatoire.');
        } elseif ($element['type_champ'] !== 'radio') {
            $validator->errors()->add("{$path}.type_champ",
                'Le type de champ doit être "radio" pour les canevas d\'appréciation.');
        }

        // Validation des IDs hashés si présents
        if (isset($element['sectionId'])) {
            $sectionIdValidator = new HashedExists(ChampSection::class);
            if (!$sectionIdValidator->passes("{$path}.sectionId", $element['sectionId'])) {
                $validator->errors()->add("{$path}.sectionId", $sectionIdValidator->message());
            }
        }

        if (isset($element['documentId'])) {
            $documentIdValidator = new HashedExists(Document::class);
            if (!$documentIdValidator->passes("{$path}.documentId", $element['documentId'])) {
                $validator->errors()->add("{$path}.documentId", $documentIdValidator->message());
            }
        }

        // Meta options obligatoires
        if (!isset($element['meta_options']) || !is_array($element['meta_options'])) {
            $validator->errors()->add("{$path}.meta_options",
                'Les options métadonnées sont obligatoires pour les champs.');
        } else {
            $this->validateMetaOptions($element, $path, $validator);
        }
    }

    /**
     * Valide un élément de type section
     */
    private function validateSectionElement($element, $path, $validator)
    {
        // Intitulé obligatoire
        if (!isset($element['intitule']) || !is_string($element['intitule']) || strlen($element['intitule']) > 255) {
            $validator->errors()->add("{$path}.intitule",
                'L\'intitulé de la section est obligatoire et ne doit pas dépasser 255 caractères.');
        }

        // Validation des IDs hashés si présents
        if (isset($element['parentSectionId'])) {
            $parentSectionIdValidator = new HashedExists(ChampSection::class);
            if (!$parentSectionIdValidator->passes("{$path}.parentSectionId", $element['parentSectionId'])) {
                $validator->errors()->add("{$path}.parentSectionId", $parentSectionIdValidator->message());
            }
        }

        if (isset($element['documentId'])) {
            $documentIdValidator = new HashedExists(Document::class);
            if (!$documentIdValidator->passes("{$path}.documentId", $element['documentId'])) {
                $validator->errors()->add("{$path}.documentId", $documentIdValidator->message());
            }
        }
    }

    /**
     * Valide les meta options d'un champ
     */
    private function validateMetaOptions($element, $path, $validator)
    {
        $metaOptions = $element['meta_options'];
        $typeChamp = $element['type_champ'] ?? null;

        // Configs obligatoire
        if (!isset($metaOptions['configs']) || !is_array($metaOptions['configs'])) {
            $validator->errors()->add("{$path}.meta_options.configs",
                'La section configs est obligatoire dans les options métadonnées.');
        } else {
            // Validation spécifique pour les champs de type radio
            if ($typeChamp === 'radio') {
                if (!isset($metaOptions['configs']['options']) || !is_array($metaOptions['configs']['options'])) {
                    $validator->errors()->add("{$path}.meta_options.configs.options",
                        'La section options est obligatoire dans configs pour les champs de type radio.');
                } elseif (empty($metaOptions['configs']['options'])) {
                    $validator->errors()->add("{$path}.meta_options.configs.options",
                        'La section options doit contenir au moins une option pour les champs de type radio.');
                } else {
                    // Vérifier que les options correspondent au guide_notation
                    $guideNotation = $this->input('guide_notation', []);
                    $configOptions = $metaOptions['configs']['options'];

                    // Créer des tableaux pour la comparaison
                    $optionsAttendues = collect($guideNotation)->map(function ($item) {
                        return [
                            'label' => $item['libelle'] ?? '',
                            'value' => $item['appreciation'] ?? ''
                        ];
                    })->sortBy('value')->values()->toArray();

                    $optionsFournies = collect($configOptions)->map(function ($item) {
                        return [
                            'label' => $item['label'] ?? '',
                            'value' => $item['value'] ?? ''
                        ];
                    })->sortBy('value')->values()->toArray();

                    if ($optionsAttendues !== $optionsFournies) {
                        $validator->errors()->add("{$path}.meta_options.configs.options",
                            'Les options de configs.options doivent correspondre exactement aux entrées du guide_notation (appreciation → value, libelle → label).');
                    }
                }
            }
        }

        // Conditions obligatoire
        if (!isset($metaOptions['conditions']) || !is_array($metaOptions['conditions'])) {
            $validator->errors()->add("{$path}.meta_options.conditions",
                'La section conditions est obligatoire dans les options métadonnées.');
        } else {
            $conditions = $metaOptions['conditions'];

            if (!isset($conditions['disable']) || !is_bool($conditions['disable'])) {
                $validator->errors()->add("{$path}.meta_options.conditions.disable",
                    'Le champ disable est obligatoire et doit être un booléen.');
            }

            if (!isset($conditions['visible']) || !is_bool($conditions['visible'])) {
                $validator->errors()->add("{$path}.meta_options.conditions.visible",
                    'Le champ visible est obligatoire et doit être un booléen.');
            }
        }

        // Validation rules obligatoire
        if (!isset($metaOptions['validations_rules']) || !is_array($metaOptions['validations_rules'])) {
            $validator->errors()->add("{$path}.meta_options.validations_rules",
                'La section validations_rules est obligatoire dans les options métadonnées.');
        } else {
            // Validation spécifique pour les champs de type radio
            if ($typeChamp === 'radio') {
                if (!isset($metaOptions['validations_rules']['in']) || !is_array($metaOptions['validations_rules']['in'])) {
                    $validator->errors()->add("{$path}.meta_options.validations_rules.in",
                        'La règle "in" est obligatoire dans validations_rules pour les champs de type radio.');
                } elseif (empty($metaOptions['validations_rules']['in'])) {
                    $validator->errors()->add("{$path}.meta_options.validations_rules.in",
                        'La règle "in" doit contenir au moins une valeur pour les champs de type radio.');
                } else {
                    // Vérifier que les valeurs "in" correspondent aux appreciations du guide_notation
                    $guideNotation = $this->input('guide_notation', []);
                    $appreciationsAttendues = collect($guideNotation)->pluck('appreciation')->toArray();
                    $valeursIn = $metaOptions['validations_rules']['in'];

                    // Trier les deux tableaux pour la comparaison
                    sort($appreciationsAttendues);
                    sort($valeursIn);

                    if ($appreciationsAttendues !== $valeursIn) {
                        $validator->errors()->add("{$path}.meta_options.validations_rules.in",
                            'Les valeurs de la règle "in" doivent correspondre exactement aux appréciations définies dans guide_notation: [' .
                            implode(', ', $appreciationsAttendues) . ']');
                    }
                }
            }
        }
    }

    public function messages(): array
    {
        return [
            // Messages pour le document
            'nom.required' => 'Le nom du document est obligatoire.',
            'nom.string' => 'Le nom du document doit être une chaîne de caractères.',
            'nom.max' => 'Le nom du document ne peut pas dépasser 65535 caractères.',
            'nom.unique' => 'Ce document existe déjà.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'description.max' => 'La description ne peut pas dépasser 65535 caractères.',
            'type.required' => 'Le type de document est obligatoire.',
            'type.string' => 'Le type de document doit être une chaîne de caractères.',
            'type.in' => 'Le type de document doit être: document, formulaire, grille ou checklist.',

            // Messages pour le tableau forms
            'forms.required' => 'Le tableau des éléments du formulaire est obligatoire.',
            'forms.array' => 'Le tableau des éléments du formulaire doit être un tableau.',
            'forms.min' => 'Au moins un élément du formulaire est requis.',

            // Messages pour les éléments
            'forms.*.element_type.required' => 'Le type d\'élément est obligatoire.',
            'forms.*.element_type.in' => 'Le type d\'élément doit être: field ou section.',
            'forms.*.ordre_affichage.required' => 'L\'ordre d\'affichage est obligatoire.',
            'forms.*.ordre_affichage.integer' => 'L\'ordre d\'affichage doit être un nombre entier.',
            'forms.*.ordre_affichage.min' => 'L\'ordre d\'affichage doit être au moins 1.',

            // Messages pour les champs
            'forms.*.label.required_if' => 'Le libellé du champ est obligatoire.',
            'forms.*.label.string' => 'Le libellé du champ doit être une chaîne de caractères.',
            'forms.*.label.max' => 'Le libellé du champ ne peut pas dépasser 500 caractères.',
            'forms.*.key.required' => 'La clé de l\'élément est obligatoire.',
            'forms.*.key.string' => 'La clé de l\'élément doit être une chaîne de caractères.',
            'forms.*.key.max' => 'La clé de l\'élément ne peut pas dépasser 255 caractères.',
            'forms.*.attribut.required_if' => 'L\'attribut du champ est obligatoire.',
            'forms.*.attribut.string' => 'L\'attribut du champ doit être une chaîne de caractères.',
            'forms.*.attribut.max' => 'L\'attribut du champ ne peut pas dépasser 255 caractères.',
            'forms.*.type_champ.required_if' => 'Le type de champ est obligatoire.',
            'forms.*.type_champ.string' => 'Le type de champ doit être une chaîne de caractères.',
            'forms.*.meta_options.required_if' => 'Les options métadonnées sont obligatoires pour les champs.',
            'forms.*.meta_options.array' => 'Les options métadonnées doivent être un tableau.',

            // Messages pour les sections
            'forms.*.intitule.required_if' => 'L\'intitulé de la section est obligatoire.',
            'forms.*.intitule.string' => 'L\'intitulé de la section doit être une chaîne de caractères.',
            'forms.*.intitule.max' => 'L\'intitulé de la section ne peut pas dépasser 255 caractères.',

            // Messages pour les éléments imbriqués
            'forms.*.elements.*.element_type.in' => 'Le type d\'élément imbriqué doit être: field ou section.',
            'forms.*.elements.*.ordre_affichage.integer' => 'L\'ordre d\'affichage de l\'élément imbriqué doit être un nombre entier.',
            'forms.*.elements.*.ordre_affichage.min' => 'L\'ordre d\'affichage de l\'élément imbriqué doit être au moins 1.',
            'forms.*.elements.*.label.required_if' => 'Le libellé du champ imbriqué est obligatoire.',
            'forms.*.elements.*.attribut.required_if' => 'L\'attribut du champ imbriqué est obligatoire.',
            'forms.*.elements.*.type_champ.required_if' => 'Le type de champ imbriqué est obligatoire.',
            'forms.*.elements.*.meta_options.required_if' => 'Les options métadonnées sont obligatoires pour les champs imbriqués.',
            'forms.*.elements.*.intitule.required_if' => 'L\'intitulé de la section imbriquée est obligatoire.',
        ];
    }
}
