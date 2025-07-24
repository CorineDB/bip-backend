<?php

namespace App\Http\Requests\idees_projet;

use App\Models\ComposantProgramme;
use App\Models\Document;
use App\Models\Financement;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class IdeeProjetRequest extends FormRequest
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
            "responsableId",
            /*"ministereId",
            "categorieId",
            "demandeurId",*/
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
        }

        $modeFinal = $this->input('etat') === 'soumettre';
        $attributRules = $this->getValidationRulesByAttribut();

        foreach ($this->input('champs', []) as $index => $champInput) {
            $champ = $this->ficheIdee->all_champs->firstWhere('id', $champInput['id'] ?? null);

            if (!$champ) {
                continue;
            }

            $valeurPresente = array_key_exists('valeur', $champInput);
            $attributPresent = array_key_exists($champ->attribut, $champInput);

            if ($modeFinal || $valeurPresente || $attributPresent) {
                // Validation pour champs.*.valeur
                if ($valeurPresente) {
                    $champRules = $this->buildValidationRulesForChamp($champ);
                    $validator->addRules([
                        "champs.$index.valeur" => $champRules,
                    ]);
                }

                // Validation pour champs.*.{attribut} (données directes par attribut)
                if ($attributPresent && isset($attributRules[$champ->attribut])) {
                    $validator->addRules([
                        "champs.$index.{$champ->attribut}" => $attributRules[$champ->attribut],
                    ]);
                }
            }
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $baseRules = [
            "id" => ["sometimes", Rule::exists('idees_projet', 'id')->whereNull('deleted_at')],
            "etat" => ["sometimes", Rule::in(['soumettre', 'brouillon'])],
            "champs" => [
                "required",
                "array",
                "min:1",
                "max:" . ($this->ficheIdee ? $this->ficheIdee->all_champs->count() : 100),
                function ($attribute, $value, $fail) {
                    if ($this->ficheIdee && count($value) > $this->ficheIdee->all_champs->count()) {
                        $fail("Le nombre de champs dépasse la limite autorisée.");
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

        return $baseRules;
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
        return [
            'sigle' => ['required', 'string', 'max:50', Rule::unique('idees_projet', 'sigle')->whereNull('deleted_at')],

            'titre_projet' => ['required', 'string', 'max:255', Rule::unique('idees_projet', 'titre_projet')->whereNull('deleted_at')],

            'duree' => ['required', 'array', 'min:2'],
            'duree.duree' => ['required', 'decimal', 'min:1'],
            'duree.unite_mesure' => ['required', 'string', 'in:an,mois,semaines'],
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
            'cout_estimatif_projet' => ['required', 'array', 'min:2'],
            'cout_estimatif_projet.montant' => ['required', 'decimal', 'min:0'],
            'cout_estimatif_projet.devise' => ['required', 'string', 'in:FCFA'],
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

            'odds' => ['required', 'array', 'min:1'],
            'odds.*' => [
                'required',
                Rule::exists('odds', 'id')->whereNull('deleted_at'),
            ],
            'cibles' => ['required', 'array', 'min:1'],
            'cibles.*' => [
                'required',
                Rule::exists('cibles', 'id')->whereNull('deleted_at'),
            ],
            'departements' => ['required', 'array', 'min:1'],
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
            'orientations_strategiques' => ['required', 'array', 'min:1'],
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
            'objectifs_strategiques' => ['required', 'array', 'min:1'],
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
            'resultats_strategiques' => ['required', 'array', 'min:1'],
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

            'sources_financement' => ['required', 'array', 'min:1'],
            'sources_financement.*' => [
                'required',
                function ($attribute, $value, $fail) {
                    $exists = Financement::where('id', $value)->where('type', 'source')
                        ->exists();

                    if (!$exists) {
                        $fail('La source de financement inconnu');
                    }
                }
            ]
        ];
    }
}
