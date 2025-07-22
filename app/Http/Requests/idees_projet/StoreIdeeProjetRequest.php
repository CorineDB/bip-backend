<?php

namespace App\Http\Requests\IdeesProjet;

use App\Models\Document;
use Illuminate\Foundation\Http\FormRequest;

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
            "ministereId",
            "categorieId",
            "responsableId",
            "demandeurId",
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
        ->with(['sections.champs', 'champs'])
        ->first();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        if (!$this->ficheIdee) {
            return [];
        }

        $rules = [];
        $tousLesChamps = collect();

        // Collecter tous les champs de la fiche
        foreach ($this->ficheIdee->sections as $section) {
            $tousLesChamps = $tousLesChamps->merge($section->champs);
        }
        $tousLesChamps = $tousLesChamps->merge($this->ficheIdee->champs);

        // Générer les règles de validation pour chaque champ
        foreach ($tousLesChamps as $champ) {
            $fieldRules = $this->buildValidationRulesForChamp($champ);
            if (!empty($fieldRules)) {
                $rules[$champ->attribut] = $fieldRules;
            }
        }

        return $rules;
    }

    /**
     * Construire les règles de validation pour un champ
     */
    private function buildValidationRulesForChamp($champ): array
    {
        $rules = [];

        // Règle required
        $isRequired = $champ->meta_options['validations_rules']['required'] ?? false;
        if ($isRequired) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }

        // Règles selon le type de champ
        switch ($champ->type_champ) {
            case 'email':
                $rules[] = 'email';
                break;

            case 'number':
                $rules[] = 'numeric';
                break;

            case 'date':
                $rules[] = 'date';
                break;

            case 'text':
            case 'textarea':
                $rules[] = 'string';
                if (isset($champ->meta_options['validations_rules']['max_length'])) {
                    $rules[] = 'max:' . $champ->meta_options['validations_rules']['max_length'];
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
                $rules[] = 'boolean';
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
                break;
        }

        // Règles personnalisées depuis meta_options
        if (isset($champ->meta_options['validations_rules']['custom_rules'])) {
            $customRules = $champ->meta_options['validations_rules']['custom_rules'];
            if (is_array($customRules)) {
                $rules = array_merge($rules, $customRules);
            }
        }

        return $rules;
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

            $messages["{$attribut}.required"] = "Le champ '{$label}' est obligatoire.";
            $messages["{$attribut}.email"] = "Le champ '{$label}' doit être un email valide.";
            $messages["{$attribut}.numeric"] = "Le champ '{$label}' doit être un nombre.";
            $messages["{$attribut}.date"] = "Le champ '{$label}' doit être une date valide.";
            $messages["{$attribut}.string"] = "Le champ '{$label}' doit être du texte.";
            $messages["{$attribut}.boolean"] = "Le champ '{$label}' doit être vrai ou faux.";
            $messages["{$attribut}.file"] = "Le champ '{$label}' doit être un fichier.";
            $messages["{$attribut}.in"] = "La valeur sélectionnée pour '{$label}' n'est pas valide.";
            $messages["{$attribut}.max"] = "Le champ '{$label}' ne doit pas dépasser :max caractères.";
            $messages["{$attribut}.mimes"] = "Le fichier '{$label}' doit être de type: :values.";
        }

        return $messages;
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        $attributes = [];

        if (!$this->ficheIdee) {
            return $attributes;
        }

        $tousLesChamps = collect();
        foreach ($this->ficheIdee->sections as $section) {
            $tousLesChamps = $tousLesChamps->merge($section->champs);
        }
        $tousLesChamps = $tousLesChamps->merge($this->ficheIdee->champs);

        // Mapper les attributs vers leurs labels
        foreach ($tousLesChamps as $champ) {
            $attributes[$champ->attribut] = $champ->label;
        }

        return $attributes;
    }
}
