<?php

namespace App\Http\Requests\documents;

use App\Enums\EnumTypeChamp;
use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateOrUpdateCanevasChecklistSuiviRapportPrefaisabiliteRequest extends FormRequest
{
    private $canevas_appreciation_tdr = null;

    public function authorize(): bool
    {
        return true;
    }

    public function prepareForValidation()
    {
        $this->canevas_appreciation_tdr = Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-checklist-suivi-rapport-prefaisabilite');
        })
        ->where('type', 'formulaire')
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
                            $query->where('slug', 'checklist-mesures-adaptation-haut-risque');
                        })->when($this->canevas_appreciation_tdr, function($query){
                            $query->where("id","<>", $this->canevas_appreciation_tdr->id);
                        })->exists();

                    if ($exists) {
                        $fail('The nom has already been taken for documents in this category.');
                    }
                }
            ],
            'description' => 'nullable|string|max:65535',
            'type' => ['required', 'string', Rule::in(['document', 'formulaire', 'grille', 'checklist'])],
            'categorieId' => 'required|exists:categories_document,id',
            // Forms array - structure flexible avec validation récursive
            'forms' => 'required|array|min:1',
            'forms.*' => 'required|array',
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
        });
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

            // Validation de l'ordre d'affichage
            if (!isset($element['ordre_affichage']) || !is_integer($element['ordre_affichage']) || $element['ordre_affichage'] < 1) {
                $validator->errors()->add("{$currentPath}.ordre_affichage",
                    'L\'ordre d\'affichage est obligatoire et doit être un entier positif.');
            }

            // Validation de la clé selon le type d'élément
            if ($element['element_type'] === 'field') {
                // Pour les fields, on utilise 'attribut'
                if (!isset($element['attribut']) || !is_string($element['attribut']) || strlen($element['attribut']) > 255) {
                    $validator->errors()->add("{$currentPath}.attribut",
                        'L\'attribut est obligatoire pour les champs et ne doit pas dépasser 255 caractères.');
                }
            } elseif ($element['element_type'] === 'section') {
                // Pour les sections, on utilise 'key'
                if (!isset($element['key']) || !is_string($element['key']) || strlen($element['key']) > 255) {
                    $validator->errors()->add("{$currentPath}.key",
                        'La clé est obligatoire pour les sections et ne doit pas dépasser 255 caractères.');
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
        if (!isset($element['label']) || !is_string($element['label']) || strlen($element['label']) > 255) {
            $validator->errors()->add("{$path}.label",
                'Le libellé du champ est obligatoire et ne doit pas dépasser 255 caractères.');
        }

        // L'attribut est déjà validé dans validateFormsStructure

        // Type de champ obligatoire
        if (!isset($element['type_champ']) || !is_string($element['type_champ'])) {
            $validator->errors()->add("{$path}.type_champ",
                'Le type de champ est obligatoire.');
        }

        // Meta options obligatoires
        if (!isset($element['meta_options']) || !is_array($element['meta_options'])) {
            $validator->errors()->add("{$path}.meta_options",
                'Les options métadonnées sont obligatoires pour les champs.');
        } else {
            $this->validateMetaOptions($element['meta_options'], $path, $validator);
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
    }

    /**
     * Valide les meta options d'un champ
     */
    private function validateMetaOptions($metaOptions, $path, $validator)
    {
        // Configs obligatoire
        if (!isset($metaOptions['configs']) || !is_array($metaOptions['configs'])) {
            $validator->errors()->add("{$path}.meta_options.configs",
                'La section configs est obligatoire dans les options métadonnées.');
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
            'categorieId.required' => 'La catégorie est obligatoire.',
            'categorieId.integer' => 'La catégorie doit être un nombre entier.',
            'categorieId.exists' => 'La catégorie sélectionnée n\'existe pas.',

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
            'forms.*.label.max' => 'Le libellé du champ ne peut pas dépasser 255 caractères.',
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