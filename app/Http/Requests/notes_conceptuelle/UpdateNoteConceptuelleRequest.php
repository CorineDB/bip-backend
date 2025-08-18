<?php

namespace App\Http\Requests\notes_conceptuelle;

use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Contracts\DocumentRepositoryInterface;

class UpdateNoteConceptuelleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'est_soumise' => 'sometimes|boolean',
            'champs' => 'sometimes|array|min:1',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Seulement valider les champs si ils sont fournis
            if ($this->has('champs')) {
                $this->validateChampsAgainstCanevas($validator);
            }
        });
    }

    /**
     * Valider les champs soumis contre le canevas de rédaction (copie de StoreNoteConceptuelleRequest)
     */
    private function validateChampsAgainstCanevas($validator): void
    {
        // Récupérer le canevas de rédaction de note conceptuelle
        $documentRepository = app(DocumentRepositoryInterface::class);
        $canevasNoteConceptuelle = $documentRepository->getModel()->where([
            'type' => 'formulaire'
        ])->whereHas('categorie', function($query) {
            $query->where('slug', 'canevas-redaction-note-conceptuelle');
        })->orderBy('created_at', 'desc')->first();

        if (!$canevasNoteConceptuelle) {
            $validator->errors()->add('canevas', 'Canevas de rédaction de note conceptuelle non trouvé.');
            return;
        }

        $estSoumise = $this->input('est_soumise', false);
        $champsData = $this->input('champs', []);

        // Récupérer TOUS les champs du canevas (y compris ceux dans les sections)
        $champsDefinitions = $this->extractAllFields($canevasNoteConceptuelle->all_champs);

        // Valider chaque champ soumis
        foreach ($champsData as $attribut => $valeur) {
            $champDefinition = $champsDefinitions->get($attribut);

            if (!$champDefinition) {
                $validator->errors()->add("champs.{$attribut}", "Le champ '{$attribut}' n'est pas défini dans le canevas.");
                continue;
            }

            $this->validateFieldValue($validator, $attribut, $valeur, $champDefinition, $estSoumise);
        }

        // Si la note est soumise, vérifier que tous les champs requis sont présents
        if ($estSoumise) {
            $champsManquants = [];
            foreach ($champsDefinitions as $attribut => $champDefinition) {
                $metaOptions = $champDefinition->meta_options;
                $isRequired = $metaOptions['validations_rules']['required'] ?? false;

                if ($isRequired && (!isset($champsData[$attribut]) || $champsData[$attribut] === '' || $champsData[$attribut] === null)) {
                    // Message dynamique basé sur les propriétés du champ
                    $validationRules = $metaOptions['validations_rules'];
                    $label = $champDefinition->label;
                    $placeholder = $champDefinition->placeholder ?? '';
                    $min = $validationRules['min'] ?? null;
                    $max = $validationRules['max'] ?? null;

                    $message = "Le champ '{$label}' est obligatoire pour soumettre la note conceptuelle.";

                    if ($placeholder) {
                        $message .= " {$placeholder}";
                    }

                    if ($min && $max) {
                        $message .= " (Entre {$min} et {$max} caractères requis)";
                    } elseif ($min) {
                        $message .= " (Minimum {$min} caractères requis)";
                    } elseif ($max) {
                        $message .= " (Maximum {$max} caractères autorisés)";
                    }

                    $validator->errors()->add("champs.{$attribut}", $message);
                }
            }
        }
    }

    /**
     * Valider une valeur de champ spécifique
     */
    private function validateFieldValue($validator, $attribut, $valeur, $champDefinition, $estSoumise): void
    {
        $metaOptions = $champDefinition->meta_options;
        $validationRules = $metaOptions['validations_rules'] ?? [];
        $label = $champDefinition->label;

        // Construire les informations sur les contraintes pour les messages d'erreur
        $constraints = [];
        if (isset($validationRules['min'])) {
            $constraints[] = "minimum {$validationRules['min']} caractères";
        }
        if (isset($validationRules['max'])) {
            $constraints[] = "maximum {$validationRules['max']} caractères";
        }
        if (isset($validationRules['string']) && $validationRules['string']) {
            $constraints[] = "format texte";
        }
        if (isset($validationRules['array']) && $validationRules['array']) {
            $constraints[] = "format liste/tableau";
        }
        if (isset($validationRules['required']) && $validationRules['required']) {
            $constraints[] = "obligatoire";
        }

        $constraintText = !empty($constraints) ? " (Contraintes: " . implode(', ', $constraints) . ")" : "";

        // Vérifier si le champ est requis
        $isRequired = $validationRules['required'] ?? false;
        if ($estSoumise && $isRequired && (is_null($valeur) || $valeur === '')) {
            $validator->errors()->add("champs.{$attribut}",
                "Le champ '{$label}' est obligatoire pour soumettre la note conceptuelle.{$constraintText}"
            );
            return;
        }

        // Si la valeur est vide et pas obligatoire, pas besoin de valider plus
        if (is_null($valeur) || $valeur === '') {
            return;
        }

        // Validation des types
        if (isset($validationRules['string']) && $validationRules['string'] && !is_string($valeur)) {
            $validator->errors()->add("champs.{$attribut}",
                "Le champ '{$label}' doit être une chaîne de caractères.{$constraintText}"
            );
            return;
        }

        if (isset($validationRules['array']) && $validationRules['array'] && !is_array($valeur)) {
            $validator->errors()->add("champs.{$attribut}",
                "Le champ '{$label}' doit être un tableau/liste.{$constraintText}"
            );
            return;
        }

        // Validation des longueurs/tailles avec messages détaillés
        if (isset($validationRules['min'])) {
            $min = $validationRules['min'];
            if (is_string($valeur) && strlen($valeur) < $min) {
                $actual = strlen($valeur);
                $validator->errors()->add("champs.{$attribut}",
                    "Le champ '{$label}' doit contenir au moins {$min} caractères (actuellement: {$actual}).{$constraintText}"
                );
            } elseif (is_array($valeur) && count($valeur) < $min) {
                $actual = count($valeur);
                $validator->errors()->add("champs.{$attribut}",
                    "Le champ '{$label}' doit contenir au moins {$min} éléments (actuellement: {$actual}).{$constraintText}"
                );
            }
        }

        if (isset($validationRules['max'])) {
            $max = $validationRules['max'];
            if (is_string($valeur) && strlen($valeur) > $max) {
                $actual = strlen($valeur);
                $validator->errors()->add("champs.{$attribut}",
                    "Le champ '{$label}' ne peut pas dépasser {$max} caractères (actuellement: {$actual}).{$constraintText}"
                );
            } elseif (is_array($valeur) && count($valeur) > $max) {
                $actual = count($valeur);
                $validator->errors()->add("champs.{$attribut}",
                    "Le champ '{$label}' ne peut pas contenir plus de {$max} éléments (actuellement: {$actual}).{$constraintText}"
                );
            }
        }
    }

    /**
     * Extraire tous les champs du canevas, y compris ceux dans les sections imbriquées
     */
    private function extractAllFields($elements): \Illuminate\Support\Collection
    {
        $fields = collect();

        foreach ($elements as $element) {
            if ($element['element_type'] === 'field') {
                $fields->put($element['attribut'], (object) $element);
            } elseif ($element['element_type'] === 'section' && isset($element['elements'])) {
                // Récursion pour les sections imbriquées
                $nestedFields = $this->extractAllFields($element['elements']);
                $fields = $fields->merge($nestedFields);
            }
        }

        return $fields;
    }
}