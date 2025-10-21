<?php

namespace App\Http\Requests\idees_projet;

use App\Models\Arrondissement;
use App\Models\CategorieProjet;
use App\Models\Cible;
use App\Models\Commune;
use App\Models\ComposantProgramme;
use App\Models\Departement;
use App\Models\Document;
use App\Models\Financement;
use App\Models\Odd;
use App\Models\Secteur;
use App\Models\Village;
use App\Rules\HashedExists;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\Support\Str;

class StoreIdeeProjetRequest extends FormRequest
{

    private ?Document $ficheIdee = null;

    public function authorize(): bool
    {
        return auth()->check();
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

        $modeFinal = $this->input('est_soumise');
        $attributRules = $this->getValidationRulesByAttribut();

        // Si mode final, valider tous les champs pré-connus obligatoires
        if ($modeFinal) {
            $this->validateSubmissionCompleteness($validator);
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
        //$this->validateRequiredRelations($validator);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'est_soumise' => ['required', 'boolean'],
            'champs' => ["required", "array", "min:1"],
            'champs.titre_projet' => ['required', 'string', 'max:255', Rule::unique('idees_projet', 'titre_projet')->whereNull('deleted_at')],
        ];

        if ($this->ficheIdee) {
            $defaultRules = $this->getValidationRulesByAttribut();
            $validationRules = $this->ficheIdee->all_champs->mapWithKeys(function ($champ) use ($defaultRules) {
                $attribut = $champ['attribut'];
                $rulesArray = $champ['meta_options']['validations_rules'] ?? [];

                // Convert the validation_rules associative array to rule strings
                $validationRules = collect($rulesArray)->map(function ($value, $key) {
                    if (is_bool($value)) {
                        return $value ? (($key == "required" && request("est_soumise")) ? $key : null) : null; // skip false rules
                    }

                    if (is_array($value)) {
                        return $key . ':' . implode(',', $value); // e.g. in:foo,bar
                    }

                    return "{$key}:{$value}";
                })->filter()->values();

                // Merge with fixed global rules (e.g., 'string', 'sometimes')
                if (isset($defaultRules[$attribut])) {
                    $validationRules = $validationRules->merge($defaultRules[$attribut]);
                }

                // Ajouter les règles sous la clé "champs.attribut"
                $validationRules = ["champs.{$attribut}" => $validationRules->toArray()];

                // Si des sous-règles existent, les ajouter aussi (ex: cibles.*)
                //if (isset($defaultRules["{$attribut}."])) {

                foreach ($defaultRules as $key => &$rule) {
                    if (Str::startsWith($key, "{$attribut}.") && $key !== "{$attribut}") {
                        //$rule = array_merge(['sometimes'], (array) $rule);
                        $validationRules["champs.{$key}"] =  (array) $rule;
                    }
                }

                return $validationRules;

                // ✅ prefix with champs.
                return ["champs.{$attribut}" => $validationRules->toArray()];
            })->toArray();

            if (isset($validationRules["titre_projet"])) {

                $rules["champs.titre_projet"] = array_merge(
                    $rules["champs.titre_projet"] ?? [],
                    $validationRules["titre_projet"]
                );
            }

            $rules = array_merge($validationRules, $rules);
        }
        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        $messages = [];

        if ($this->ficheIdee) {
            foreach ($this->ficheIdee->all_champs as $champ) {
                $key = "champs.{$champ->attribut}";

                $messages["{$key}.required"] = "Le champ « {$champ->label} » est requis.";
                $messages["{$key}.integer"] = "Le champ « {$champ->label} » doit etre un entier.";
                $messages["{$key}.numeric"] = "Le champ « {$champ->label} » doit etre un nombre.";
                $messages["{$key}.max"] = "Le champ « {$champ->label} » dépasse la taille maximale.";
                $messages["{$key}.min"] = "Le champ « {$champ->label} » est trop court.";
                $messages["{$key}.string"] = "Le champ « {$champ->label} » doit être une chaîne de caractères.";

                $defaultRules = $this->getValidationRulesByAttribut($this->route("idee_projet"));

                foreach ($defaultRules as $sub_key => &$rule) {
                    $sub_key = "champs.{$sub_key}";
                    if (Str::startsWith($sub_key, "{$key}.") && $sub_key !== $key) {

                        //$rule = array_merge(['sometimes'], (array) $rule);
                        $messages["{$sub_key}.required"] = "Le champ « {$champ->label} » est requis.";
                        $messages["{$sub_key}.integer"] = "Le champ « {$champ->label} » doit etre un entier.";
                        $messages["{$sub_key}.numeric"] = "Le champ « {$champ->label} » doit etre un nombre.";
                        $messages["{$sub_key}.max"] = "Le champ « {$champ->label} » dépasse la taille maximale.";
                        $messages["{$sub_key}.min"] = "Le champ « {$champ->label} » est trop court.";
                        $messages["{$sub_key}.string"] = "Le champ « {$champ->label} » doit être une chaîne de caractères.";
                    }
                }
            }
        }

        return $messages;
    }

    private function getValidationRulesByAttribut(): array
    {
        $isSubmissionMode = $this->input('est_soumise');

        $baseRules = [
            'sigle' => ["nullable", 'string', 'max:50', Rule::unique('idees_projet', 'sigle')->whereNull('deleted_at')],

            'duree' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'array', 'min:0'],
            'duree.*' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'integer', 'min:1'],/*
            'duree.duree' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'integer', 'min:1'],
            'duree.unite_mesure' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'in:an,mois,semaines'],*/
            'description' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],

            'contraintes' => ["nullable", 'string', 'max:65535'],
            'description_projet' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],
            'description_extrants' => ["nullable", 'string', 'max:65535'],
            'echeancier' => ["nullable", 'string', 'max:65535'],
            'caracteristiques_techniques' => ["nullable", 'string', 'max:65535'],
            'impact_environnement' => ["nullable", 'string', 'max:65535'],
            'aspect_organisationnel' => ["nullable", 'string', 'max:65535'],
            'estimation_couts' => ["nullable", 'string', /* 'numeric', 'min:0', */],
            'risques_immediats' => ["nullable", 'string', 'max:65535'],
            'conclusions' => ["nullable", 'string', 'max:65535'],
            'sommaire' => ["nullable", 'string', 'max:65535'],
            'constraintes' => ["nullable", 'string', 'max:65535'],
            'cout_estimatif_projet' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'array', 'min:2'],
            'cout_estimatif_projet.montant' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'numeric', 'min:0'],
            'cout_estimatif_projet.devise' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'in:FCFA'],

            'cout_dollar_americain' => ["nullable", 'numeric', 'min:0'],
            'cout_dollar_canadien' => ["nullable", 'numeric', 'min:0'],
            'cout_euro' => ["nullable", 'numeric', 'min:0'],
            'situation_actuelle' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],
            'situation_desiree' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],
            'fondement' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],
            'origine' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],
            'objectif_general' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],

            'objectifs_specifiques' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'array', 'min:0'],
            'objectifs_specifiques.*' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],

            'resultats_attendus' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'array', 'min:0'],
            'resultats_attendus.*' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],

            'constats_majeurs' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],
            'parties_prenantes' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'array', 'min:0'],
            'parties_prenantes.*' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],
            'public_cible' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'string', 'max:65535'],

            'categorieId' => [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", new HashedExists(CategorieProjet::class) /*Rule::exists('categories_projet', 'id')->whereNull('deleted_at')*/],
            'secteurId' => [
                $isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable",
                new HashedExists(Secteur::class),
                /*Rule::exists('secteurs', 'id')->where("type", 'sous-secteur')->whereNull('deleted_at')*/
                /* function ($attribute, $value, $fail) {
                    $exists = Secteur::findByHashedId('id', $value)->where("type", 'sous-secteur')->whereNull('deleted_at')
                        ->exists();
                    if (!$exists) {
                        $fail('Le Sous-secteur est inconnu');
                    }
                } */
            ],
            'odds' => $isSubmissionMode ? [$isSubmissionMode ? Rule::requiredIf($isSubmissionMode) : "nullable", 'array', 'min:1'] : ['nullable', 'array'],
            'odds.*' => [
                Rule::requiredIf($isSubmissionMode),
                new HashedExists(Odd::class),

            ],
            'cibles' => $isSubmissionMode ? [Rule::requiredIf($isSubmissionMode), 'array', 'min:1'] : ['nullable', 'array'],
            'cibles.*' => [
                'required',
                new HashedExists(Cible::class),
            ],
            'departements' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['array', 'min:0'],
            'departements.*' => [
                'required',
                new HashedExists(Departement::class),
            ],
            'communes' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['array', 'min:0'],
            'communes.*' => [
                'required',
                new HashedExists(Commune::class),
            ],
            'arrondissements' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['array', 'min:0'],
            'arrondissements.*' => [
                'required',
                new HashedExists(Arrondissement::class),
            ],
            'villages' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['array', 'min:0'],
            'villages.*' => [
                'required',
                new HashedExists(Village::class),
            ],
            'orientations_strategiques' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array', 'min:0'],
            'orientations_strategiques.*' => [
                'required',
                new HashedExists(ComposantProgramme::class),
                /*function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::findByHashedId('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'orientation-strategique-pnd');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Orientation strategique inconnu');
                    }
                }*/
            ],
            'objectifs_strategiques' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array', 'min:0'],
            'objectifs_strategiques.*' => [
                'required',
                new HashedExists(ComposantProgramme::class),
                /*function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::findByHashedId('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'objectif-strategique-pnd');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Objectif strategique inconnu');
                    }
                }*/
            ],
            'resultats_strategiques' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array', 'min:0'],
            'resultats_strategiques.*' => [
                'required', new HashedExists(ComposantProgramme::class),
                /*function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'resultats-strategique-pnd');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Resultats strategique inconnu');
                    }
                }*/
            ],

            'sources_financement' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array', 'min:0'],
            'sources_financement.*' => [
                'required',
                new HashedExists(ComposantProgramme::class),
                /* function ($attribute, $value, $fail) {
                    $exists = Financement::where('id', $value)->where('type', 'source')
                        ->exists();

                    if (!$exists) {
                        $fail('La source de financement inconnu');
                    }
                } */
            ],
            'axes_pag' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array', 'min:0'],
            'axes_pag.*' => [
                'required',
                new HashedExists(ComposantProgramme::class),
                /*function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'axe-pag');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Axe du pag connu');
                    }
                }*/
            ],
            'actions_pag' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array', 'min:0'],
            'actions_pag.*' => [
                'required',
                new HashedExists(ComposantProgramme::class),
                /*function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'action-pag');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Action du pag connu');
                    }
                }*/
            ],
            'piliers_pag' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['nullable', 'array', 'min:0'],
            'piliers_pag.*' => [
                'required',
                new HashedExists(ComposantProgramme::class),
                /*function ($attribute, $value, $fail) {
                    $exists = ComposantProgramme::where('id', $value)
                        ->whereHas('typeProgramme', function ($query) {
                            $query->where('slug', 'pilier-pag');
                        })
                        ->exists();

                    if (!$exists) {
                        $fail('Pilier du pag connu');
                    }
                }*/
            ],
        ];

        return $baseRules;
    }
}
