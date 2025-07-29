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

        //dd($this->ficheIdee->all_champs->pluck("attribut", "meta_options.validations_rules"));

        $allFieldRules = $this->getValidationRulesByAttribut();
        $champs = $this->input('champs', []);

        // Extraire uniquement les attributs définis dans les règles
        $attributsDemandes = array_keys($champs);
        $isSubmissionMode = $this->boolean('est_soumise');

        if ($isSubmissionMode) {
            // Valider tous les champs définis
            foreach ($attributsDemandes as $attribut) {
                if (isset($allFieldRules[$attribut])) {
                    $rules["champs.$attribut"] = $allFieldRules[$attribut];
                }

                // Ajouter aussi les sous-règles (ex: champs.*)
                foreach ($allFieldRules as $key => $value) {
                    if (str_starts_with($key, "$attribut.")) {
                        $rules["champs.$key"] = $value;
                    }
                }
            }
        } else {
            // Valider uniquement le premier champ trouvé
            $firstAttribut = array_key_first($champs);
            if ($firstAttribut && isset($allFieldRules[$firstAttribut])) {
                $rules["champs.$firstAttribut"] = $allFieldRules[$firstAttribut];

                // Sous-clés éventuelles (ex: champs.objectifs_specifiques.*)
                foreach ($allFieldRules as $key => $value) {
                    if (str_starts_with($key, "$firstAttribut.")) {
                        $rules["champs.$key"] = $value;
                    }
                }
            }
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return $messages = [];
    }

    private function getValidationRulesByAttribut(): array
    {
        $isSubmissionMode = $this->input('est_soumise');

        $baseRules = [
            'sigle' => ['required', 'string', 'max:50', Rule::unique('idees_projet', 'sigle')->whereNull('deleted_at')],
            'duree' => ['required', 'array'],
            'duree.*' => ['numeric'],/*
            'duree' => ['required', 'array', 'min:2'],
            'duree.duree' => ['required', 'numeric', 'min:1'],
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
            //'cout_estimatif_projet' => ['required', 'numeric', 'min:0'],
            'cout_estimatif_projet' => ['required', 'array', 'min:2'],
            'cout_estimatif_projet.montant' => ['required', 'numeric', 'min:0'],
            'cout_estimatif_projet.devise' => ['required', 'string', 'in:FCFA'],

            'cout_dollar_americain' => ['required', 'numeric', 'min:0'],
            'cout_dollar_canadien' => ['required', 'numeric', 'min:0'],
            'cout_euro' => ['required', 'numeric', 'min:0'],
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
            'departements' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['array', 'min:0'],
            'departements.*' => [
                'required',
                Rule::exists('departements', 'id')->whereNull("deleted_at")
            ],
            'communes' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['array', 'min:0'],
            'communes.*' => [
                'required',
                Rule::exists('communes', 'id')->whereNull("deleted_at")
            ],
            'arrondissements' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['array', 'min:0'],
            'arrondissements.*' => [
                'required',
                Rule::exists('arrondissements', 'id')->whereNull("deleted_at")
            ],
            'villages' => $isSubmissionMode ? ['required', 'array', 'min:1'] : ['array', 'min:0'],
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
