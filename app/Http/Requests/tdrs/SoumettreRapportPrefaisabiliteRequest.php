<?php

namespace App\Http\Requests\tdrs;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Models\Projet;
use App\Models\Notation;
use App\Models\CategorieCritere;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class SoumettreRapportPrefaisabiliteRequest extends FormRequest
{
    protected $champs = [];
    protected $projet = null;

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; //auth()->check() && in_array(auth()->user()->type, ['dpaf', 'admin']);
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

        // Récupérer le canevas de checklist de suivi
        $canevas = $this->getChecklistSuiviPrefaisabilite();
        if (!empty($canevas)) {
            // Extraire tous les IDs des champs du canevas
            $champsValides = $this->extractAllFields($canevas);
            $this->champs = collect($champsValides)->pluck('id')->filter()->toArray();
        }
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Récupérer le nombre de critères requis dynamiquement
        $categorieChecklist = \App\Models\CategorieCritere::where('slug', 'checklist-mesures-adaptation-haut-risque')
            ->withCount('criteres')
            ->first();
        $nombreCriteresRequis = $categorieChecklist ? $categorieChecklist->criteres_count : 4;

        return [
            // Action: submit (soumettre) ou draft (brouillon)
            'action' => 'required|string|in:submit,draft',

            // Fichiers requis uniquement pour la soumission finale
            'rapport' => 'required_unless:action,draft|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'proces_verbal' => 'required_unless:action,draft|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',

            // Informations cabinet requises uniquement pour la soumission finale
            "cabinet_etude" => "required_unless:action,draft|array|min:4",
            'cabinet_etude.nom_cabinet' => 'required_with:cabinet_etude|string|max:255',
            'cabinet_etude.contact_cabinet' => 'required_with:cabinet_etude|string|max:255',
            'cabinet_etude.email_cabinet' => 'required_with:cabinet_etude|email|max:255',
            'cabinet_etude.adresse_cabinet' => 'required_with:cabinet_etude|string|max:500',

            // Recommandation requise uniquement pour la soumission finale
            'recommandation' => 'required_unless:action,draft|string|max:500',

            // Checklist contrôle des adaptations pour projets à haut risque
            'checklist_controle_adaptation_haut_risque' => [Rule::requiredIf($this->projet->est_a_haut_risque), 'array'],
            /*
                'checklist_controle_adaptation_haut_risque.criteres' => [
                    'required_with:checklist_controle_adaptation_haut_risque',
                    'array',
                    $this->input('action', 'submit') === 'draft' ? 'min:0' : "size:$nombreCriteresRequis"
                ],
            */
            'checklist_controle_adaptation_haut_risque.criteres' => [
                $this->projet->est_a_haut_risque ? 'required_with:checklist_controle_adaptation_haut_risque' : 'nullable',
                'array',
                $this->input('action', 'submit') === 'draft' ? 'min:0' : "size:$nombreCriteresRequis"
            ],
            'checklist_controle_adaptation_haut_risque.criteres.*' => 'array',
            'checklist_controle_adaptation_haut_risque.criteres.*.critere_id' => 'required|integer|exists:criteres,id',

            // Les mesures sont requises seulement si action != draft
            'checklist_controle_adaptation_haut_risque.criteres.*.mesures_selectionnees' => [
                'required_unless:action,draft',
                'array',
                'min:1'
            ],
            'checklist_controle_adaptation_haut_risque.criteres.*.mesures_selectionnees.*' => 'integer|exists:notations,id',

            // Checklist de suivi de rapport de préfaisabilité
            'checklist_suivi_rapport_prefaisabilite' => [
                'required_unless:action,draft',
                'array',
                $this->input('action', 'submit') === 'draft' ? 'min:0' : 'min:' . count($this->champs)
            ],
            'checklist_suivi_rapport_prefaisabilite.*.checkpoint_id' => ['required', "in:" . implode(",", $this->champs)],
            // Les règles dynamiques seront ajoutées dans withValidator
            //'checklist_suivi_rapport_prefaisabilite.*.reponse' => 'required_unless:action,draft|string',
            //'checklist_suivi_rapport_prefaisabilite.*.commentaire' => 'required_unless:action,draft|string|min:10|max:1000',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateChecklistAgainstProjectSector($validator);
            $this->validateChecklistSuiviPrefaisabilite($validator);
        });
    }

    /**
     * Valider que les critères et mesures correspondent au secteur du projet
     */
    private function validateChecklistAgainstProjectSector(Validator $validator): void
    {
        if (!$this->projet || !$this->projet->secteur || !$this->projet->secteur->parent) {
            $validator->errors()->add('projet', 'Le projet doit avoir un secteur avec un parent défini pour valider la checklist.');
            return;
        }

        // Vérifier si la checklist est présente
        $checklist = $this->input('checklist_controle_adaptation_haut_risque');
        if (!$checklist || !isset($checklist['criteres'])) {
            return;
        }

        // Récupérer la catégorie checklist avec ses critères
        $categorieChecklist = CategorieCritere::where('slug', 'checklist-mesures-adaptation-haut-risque')
            ->with('criteres')
            ->first();

        if (!$categorieChecklist) {
            $validator->errors()->add('checklist', 'Catégorie de checklist non trouvée.');
            return;
        }

        $criteresValides = $categorieChecklist->criteres->pluck('id')->toArray();
        $totalCriteresRequis = $categorieChecklist->criteres->count();

        /* foreach ($checklist['criteres'] as $index => $critere) {
            $critereId = $critere['critere_id'] ?? null;

            // Vérifier que le critère appartient à la catégorie checklist
            if ($critereId && !in_array($critereId, $criteresValides)) {
                $validator->errors()->add(
                    "checklist_controle_adaptation_haut_risque.criteres.{$index}.critere_id",
                    "Le critère sélectionné n'appartient pas à la checklist des mesures d'adaptation."
                );
                continue;
            }

            // Vérifier les mesures sélectionnées
            $mesuresSelectionnees = $critere['mesures_selectionnees'] ?? [];

            foreach ($mesuresSelectionnees as $mesureIndex => $mesureId) {
                // Vérifier que la mesure existe, appartient au bon critère et au secteur parent
                $mesureValide = Notation::where('id', $mesureId)
                    ->where('critere_id', $critereId)
                    ->where('secteur_id', $this->projet->secteur->parent->id)
                    ->exists();

                if (!$mesureValide) {
                    $validator->errors()->add(
                        "checklist_controle_adaptation_haut_risque.criteres.{$index}.mesures_selectionnees.{$mesureIndex}",
                        "La mesure sélectionnée n'appartient pas au critère spécifié ou au secteur du projet."
                    );
                }
            }
        } */

        // Vérifier que tous les critères obligatoires sont présents pour une soumission finale
        if ($this->input('action', 'submit') === 'submit') {
            $criteresPresents = collect($checklist['criteres'])->pluck('critere_id')->toArray();
            $criteresManquants = array_diff($criteresValides, $criteresPresents);

            if (!empty($criteresManquants)) {
                $validator->errors()->add(
                    'checklist_controle_adaptation_haut_risque.criteres',
                    'Tous les critères de la checklist doivent être renseignés pour la soumission finale. Critères manquants: ' . implode(', ', $criteresManquants)
                );
            }

            // Vérifier que tous les critères présents ont des mesures sélectionnées
            foreach ($checklist['criteres'] as $index => $critere) {
                $mesuresSelectionnees = $critere['mesures_selectionnees'] ?? [];
                if (empty($mesuresSelectionnees)) {
                    $validator->errors()->add(
                        "checklist_controle_adaptation_haut_risque.criteres.{$index}.mesures_selectionnees",
                        'Pour la soumission finale, chaque critère doit avoir au moins une mesure sélectionnée.'
                    );
                }
            }
        }
    }

    /**
     * Valider la checklist de suivi de rapport de préfaisabilité
     */
    private function validateChecklistSuiviPrefaisabilite(Validator $validator): void
    {
        $checklistSuivi = $this->input('checklist_suivi_rapport_prefaisabilite');
        if (!$checklistSuivi || !is_array($checklistSuivi)) {
            return;
        }

        $estSoumise = $this->input('action', 'submit') === 'submit';
        $canevasFields = $this->getCanevasFieldsWithConfigs();


        foreach ($checklistSuivi as $index => $evaluation) {
            $checkpointId = $evaluation['checkpoint_id'] ?? null;
            $remarque = $evaluation['remarque'] ?? null;
            $explication = $evaluation['explication'] ?? null;

            // Vérifier que le checkpoint_id existe dans le canevas
            if ($checkpointId && !in_array($checkpointId, $this->champs)) {
                $validator->errors()->add(
                    "checklist_suivi_rapport_prefaisabilite.{$index}.checkpoint_id",
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
                        "checklist_suivi_rapport_prefaisabilite.{$index}.remarque",
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
            $champsManquants = array_diff($this->champs, $champsEvalues);

            if (!empty($champsManquants)) {
                $validator->errors()->add(
                    'checklist_suivi_rapport_prefaisabilite',
                    'Tous les champs de la checklist doivent être évalués pour la soumission finale. Champs manquants: ' . implode(', ', $champsManquants)
                );
            }
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'rapport.required' => 'Le fichier rapport est obligatoire.',
            'rapport.file' => 'Le rapport doit être un fichier.',
            'rapport.mimes' => 'Le rapport doit être un fichier PDF, DOC ou DOCX.',
            'rapport.max' => 'Le fichier rapport ne peut dépasser 20 MB.',

            'cabinet_etude.nom_cabinet.required' => 'Le nom du cabinet est obligatoire.',
            'cabinet_etude.nom_cabinet.string' => 'Le nom du cabinet doit être du texte.',
            'cabinet_etude.nom_cabinet.max' => 'Le nom du cabinet ne peut dépasser 255 caractères.',

            'cabinet_etude.contact_cabinet.required' => 'Le contact du cabinet est obligatoire.',
            'cabinet_etude.contact_cabinet.string' => 'Le contact du cabinet doit être du texte.',
            'cabinet_etude.contact_cabinet.max' => 'Le contact du cabinet ne peut dépasser 255 caractères.',

            'cabinet_etude.email_cabinet.required' => 'L\'email du cabinet est obligatoire.',
            'cabinet_etude.email_cabinet.email' => 'L\'email du cabinet doit être une adresse email valide.',
            'cabinet_etude.email_cabinet.max' => 'L\'email du cabinet ne peut dépasser 255 caractères.',

            'cabinet_etude.telephone_cabinet.string' => 'Le téléphone du cabinet doit être du texte.',
            'cabinet_etude.telephone_cabinet.max' => 'Le téléphone du cabinet ne peut dépasser 20 caractères.',

            'cabinet_etude.adresse_cabinet.string' => 'L\'adresse du cabinet doit être du texte.',
            'cabinet_etude.adresse_cabinet.max' => 'L\'adresse du cabinet ne peut dépasser 500 caractères.',

            // Messages pour l'action
            'action.in' => 'L\'action doit être "submit" ou "draft".',

            // Messages pour les fichiers
            'rapport.required_unless' => 'Le fichier rapport est obligatoire pour la soumission finale.',
            'proces_verbal.required_unless' => 'Le procès verbal est obligatoire pour la soumission finale.',

            // Messages pour le cabinet
            'cabinet_etude.required_unless' => 'Les informations du cabinet sont obligatoires pour la soumission finale.',
            'recommandation.required_unless' => 'La recommandation est obligatoire pour la soumission finale.',

            // Messages pour la checklist
            'checklist_controle_adaptation_haut_risque.criteres.required_with' => 'Les critères de la checklist sont obligatoires.',
            'checklist_controle_adaptation_haut_risque.criteres.*.critere_id.required' => 'L\'ID du critère est obligatoire.',
            'checklist_controle_adaptation_haut_risque.criteres.*.critere_id.exists' => 'Le critère sélectionné n\'existe pas.',
            'checklist_controle_adaptation_haut_risque.criteres.*.mesures_selectionnees.required_unless' => 'Pour soumettre le rapport, toutes les mesures doivent être sélectionnées pour chaque critère.',
            'checklist_controle_adaptation_haut_risque.criteres.*.mesures_selectionnees.*.exists' => 'Une ou plusieurs mesures sélectionnées n\'existent pas.',
            'checklist_controle_adaptation_haut_risque.criteres.*.mesures_selectionnees.*.integer' => 'L\'ID de la mesure doit être un nombre entier.',

            // Messages pour la checklist de suivi
            'checklist_suivi_rapport_prefaisabilite.required' => 'La checklist de suivi de rapport de préfaisabilité est obligatoire.',
            'checklist_suivi_rapport_prefaisabilite.array' => 'La checklist de suivi doit être un tableau.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.required_with' => 'Les évaluations des champs de la checklist de suivi sont obligatoires.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.array' => 'Les évaluations des champs doivent être un tableau.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.min' => 'Au moins une évaluation de champ est requise.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.*.champ_id.required' => 'L\'ID du champ est obligatoire.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.*.champ_id.integer' => 'L\'ID du champ doit être un nombre entier.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.*.reponse.required_unless' => 'La réponse est obligatoire pour la soumission finale.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.*.reponse.string' => 'La réponse doit être du texte.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.*.commentaire.required_unless' => 'Le commentaire est obligatoire pour la soumission finale.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.*.commentaire.string' => 'Le commentaire doit être du texte.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.*.commentaire.min' => 'Le commentaire doit contenir au moins 10 caractères.',
            'checklist_suivi_rapport_prefaisabilite.evaluations_champs.*.commentaire.max' => 'Le commentaire ne peut dépasser 1000 caractères.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'fichier_rapport' => 'fichier rapport',
            'nom_cabinet' => 'nom du cabinet',
            'contact_cabinet' => 'contact du cabinet',
            'email_cabinet' => 'email du cabinet',
            'telephone_cabinet' => 'téléphone du cabinet',
            'adresse_cabinet' => 'adresse du cabinet',
            'recommandation_adaptation' => 'recommandation d\'adaptation',
        ];
    }



    /**
     * Génère récursivement les règles à partir du canevas (version améliorée)
     */
    protected function buildRulesFromCanevas(array $fields, array $values, array $defaultRules = [], bool $estSoumise = false, string $prefix = 'champs'): array
    {
        $rules = [];

        foreach ($fields as $field) {
            // Les champs du canevas n'ont pas toujours element_type
            // Si il a un attribut et des validations, c'est un champ
            $isField = !empty($field['attribut']) && !empty($field['meta_options']['validations_rules']);
            $isSection = ($field['element_type'] ?? null) === 'section' && !empty($field['elements']);

            if (!$isField) {
                // Gestion récursive des sections
                if ($isSection) {
                    $sectionRules = $this->buildRulesFromCanevas($field['elements'], $values, $defaultRules, $estSoumise, $prefix);
                    $rules = array_merge($rules, $sectionRules);
                }
                continue;
            }

            $attribut = $field['attribut'];
            $key = $prefix . '.' . $attribut;
            $meta = $field['meta_options'] ?? [];
            $validations = $meta['validations_rules'] ?? [];

            // Vérifier la visibilité du champ
            if (!$this->isFieldVisible($field, $values)) {
                continue;
            }

            $fieldRules = [];

            // Construction des règles de base
            foreach ($validations as $rule => $value) {
                if ($rule === 'required') {
                    // Required seulement si est_soumise est true
                    if ($estSoumise && $value) {
                        $fieldRules[] = 'required';
                    } else {
                        $fieldRules[] = 'nullable';
                    }
                } elseif (is_bool($value) && $value) {
                    $fieldRules[] = $rule;
                } elseif (is_array($value)) {
                    if ($rule === 'in') {
                        // Ignorer les règles 'in' avec tableau vide - causé par datasource
                        if (!empty($value)) {
                            $fieldRules[] = Rule::in($value);
                        }
                    } elseif ($rule === 'each') {
                        // Gestion spéciale pour la règle 'each' qui contient des sous-règles
                        $fieldRules[] = 'array';
                        // Les sous-règles 'each' seront traitées séparément avec .*
                    } else {
                        // Pour les autres règles array, vérifier si ce sont des scalaires
                        $scalarValues = array_filter($value, function ($v) {
                            return is_scalar($v);
                        });
                        if (count($scalarValues) === count($value)) {
                            $fieldRules[] = $rule . ':' . implode(',', $value);
                        } else {
                            // Si contient des non-scalaires, ignorer ou traiter différemment
                            continue;
                        }
                    }
                } elseif (!is_bool($value)) {
                    $fieldRules[] = "{$rule}:{$value}";
                }
            }

            // Gestion des datasources dynamiques - TEMPORAIREMENT DESACTIVE
            // if (!empty($meta['configs']['datasource'])) {
            //     $options = $this->getDatasourceOptions($meta['configs']['datasource'], $attribut, $values);
            //     if (!empty($options)) {
            //         $fieldRules[] = Rule::in($options);
            //     }
            // }

            // Merge avec les règles par défaut
            if (isset($defaultRules[$attribut])) {
                $fieldRules = array_merge($fieldRules, (array)$defaultRules[$attribut]);
            }

            // Pour les champs array, valider chaque élément
            if (in_array('array', $fieldRules)) {
                // Gestion des règles 'each' pour les éléments du tableau
                $eachRules = $validations['each'] ?? [];
                if (!empty($eachRules)) {
                    $elementRules = [];
                    foreach ($eachRules as $eachRule => $eachValue) {
                        if (is_bool($eachValue) && $eachValue) {
                            $elementRules[] = $eachRule;
                        } elseif (is_array($eachValue) && $eachRule === 'in') {
                            // Ignorer les règles 'in' avec tableau vide - causé par datasource
                            if (!empty($eachValue)) {
                                $elementRules[] = Rule::in($eachValue);
                            }
                        } elseif (!is_bool($eachValue) && !is_array($eachValue)) {
                            $elementRules[] = "{$eachRule}:{$eachValue}";
                        }
                    }
                    $rules["{$key}.*"] = $elementRules ?: ['string'];
                } else {
                    $rules["{$key}.*"] = ['string']; // Ou autre type selon le champ
                }

                // Si c'est un select multiple avec datasource - TEMPORAIREMENT DESACTIVE
                // if (!empty($meta['configs']['datasource'])) {
                //     $options = $this->getDatasourceOptions($meta['configs']['datasource'], $attribut, $values);
                //     if (!empty($options)) {
                //         $rules["{$key}.*"] = ['required', 'string', Rule::in($options)];
                //     }
                // }
            }

            if (!empty($fieldRules)) {
                $rules[$key] = $fieldRules;
            }

            // Gestion récursive des children (si applicable)
            if (!empty($field['children']) && is_array($field['children'])) {
                $childValues = $values[$attribut] ?? [];
                $childRules = $this->buildRulesFromCanevas($field['children'], $childValues, $defaultRules, $estSoumise, $key);
                $rules = array_merge($rules, $childRules);
            }
        }

        return $rules;
    }

    /**
     * Aplati toutes les sections pour ne garder que les champs.
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

    /**
     * Vérifie si un champ doit être visible selon ses conditions.
     */
    private function isFieldVisible(array $champ, array $values): bool
    {
        $conditions = $champ['meta_options']['conditions'] ?? [];

        // Vérifier d'abord si le champ est explicitement marqué comme invisible
        $baseVisibility = $conditions['visible'] ?? true;
        if ($baseVisibility === false) {
            // Vérifier s'il y a des conditions de visibilité (dépendances)
            $hasConditions = !empty($conditions['conditions']);

            if ($hasConditions) {
                // Le champ est invisible par défaut mais peut devenir visible selon les conditions
                foreach ($conditions['conditions'] ?? [] as $condition) {
                    $field = $condition['field'] ?? null;
                    $operator = $condition['operator'] ?? null;

                    if ($field && $operator === 'not_empty') {
                        $fieldValue = $values[$field] ?? null;
                        if (!empty($fieldValue)) {
                            return true; // Le champ devient visible
                        }
                    }
                }
                return false; // Reste invisible car conditions non remplies
            } else {
                // Champ avec visible: false SANS conditions -> vraiment caché
                return false;
            }
        }

        // Par défaut, tous les champs sont visibles
        // depends_on n'affecte PAS la visibilité, juste la validation
        return true;
    }

    /**
     * Transforme le JSON meta_options.validations_rules en règles Laravel.
     */
    private function formatValidationRules(array $rulesArray, string $attribut, array $defaultRules, bool $estSoumise): array
    {
        $champ = $this->getCurrentField($attribut);
        $meta = $champ['meta_options'] ?? [];
        $configs = $meta['configs'] ?? [];

        $validationRules = collect();

        // Gestion des champs array/tableau
        if (!empty($rulesArray['array'])) {
            $validationRules->push('array');

            // Pour les champs select multiples avec datasource
            if (!empty($configs['datasource']) && !empty($configs['multiple'])) {
                $options = $this->getDatasourceOptions($configs['datasource'], $attribut);
                if (!empty($options)) {
                    return [
                        "champs.{$attribut}" => $validationRules->push($estSoumise && ($rulesArray['required'] ?? false) ? 'required' : 'nullable')->toArray(),
                        "champs.{$attribut}.*" => ['required', 'string', Rule::in($options)]
                    ];
                }
            }
        }

        // Gestion des champs select avec datasource
        if (!empty($configs['datasource']) && empty($configs['multiple'])) {
            $options = $this->getDatasourceOptions($configs['datasource'], $attribut);
            if (!empty($options)) {
                $validationRules->push(Rule::in($options));
            }
        }

        // Gestion des champs select statiques
        if (!empty($rulesArray['in']) && is_array($rulesArray['in'])) {
            $validationRules->push(Rule::in($rulesArray['in']));
        }

        // Transformation des autres règles
        foreach ($rulesArray as $key => $value) {
            if ($key === 'required') {
                // Required seulement si est_soumise est true
                if ($estSoumise && $value) {
                    $validationRules->prepend('required');
                } else {
                    $validationRules->prepend('nullable');
                }
            } elseif (in_array($key, ['string', 'numeric', 'boolean', 'array'])) {
                if ($value) {
                    $validationRules->push($key);
                }
            } elseif (in_array($key, ['min', 'max', 'size'])) {
                $validationRules->push("{$key}:{$value}");
            } elseif ($key === 'in' && !is_array($value)) {
                // Skip, déjà géré plus haut
                continue;
            } elseif (is_string($value) && !is_numeric($key)) {
                $validationRules->push($value);
            } elseif (is_bool($value) && $value) {
                $validationRules->push($key);
            }
        }

        // Ajout des règles par défaut
        if (isset($defaultRules[$attribut])) {
            $validationRules = $validationRules->merge($defaultRules[$attribut]);
        }

        $finalRules = ["champs.{$attribut}" => $validationRules->filter()->unique()->toArray()];

        // Règles pour les sous-champs
        foreach ($defaultRules as $key => $rule) {
            if (Str::startsWith($key, "{$attribut}.") && $key !== $attribut) {
                $finalRules["champs.{$key}"] = (array) $rule;
            }
        }

        return $finalRules;
    }

    /**
     * Récupérer les options depuis une datasource (version améliorée)
     */
    private function getDatasourceOptions(string $datasource, string $attribut, array $values = []): array
    {
        try {
            // Construire l'URL complète si nécessaire
            $url = Str::startsWith($datasource, 'http') ? $datasource : url($datasource);

            // Gestion des dépendances (depends_on) - Version améliorée de GPT
            $champ = $this->getCurrentField($attribut);
            $dependsOn = $champ['meta_options']['configs']['depends_on'] ?? null;

            if ($dependsOn) {
                $dependsValue = $values[$dependsOn] ?? null;
                if ($dependsValue) {
                    // Support pour multiple parent_ids comme suggéré par GPT
                    $parentIds = is_array($dependsValue) ? implode(',', $dependsValue) : $dependsValue;
                    $url .= (Str::contains($url, '?') ? '&' : '?') . "parent_ids={$parentIds}";
                }
            }

            $response = Http::timeout(5)->get($url);

            if ($response->successful()) {
                $data = $response->json();

                // Extraire les IDs selon la structure de réponse
                if (isset($data['data'])) {
                    return collect($data['data'])->pluck('id')->filter()->toArray();
                } elseif (is_array($data)) {
                    return collect($data)->pluck('id')->filter()->toArray();
                }
            }
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer la validation
            \Log::warning("Erreur lors de la récupération de datasource: {$datasource}", [
                'error' => $e->getMessage(),
                'attribut' => $attribut
            ]);
        }

        return [];
    }

    /**
     * Récupérer le champ actuel depuis le canevas
     */
    private function getCurrentField(string $attribut): array
    {
        static $fields = null;

        if ($fields === null) {
            $documentRepository = app(DocumentRepositoryInterface::class);
            $canevas = $documentRepository->getModel()
                ->where('type', 'formulaire')
                ->whereHas('categorie', fn($q) => $q->where('slug', 'checklist-mesures-adaptation-haut-risque'))
                ->orderBy('created_at', 'desc')
                ->first();

            if ($canevas && $canevas->all_champs) {
                // Convertir en array si c'est une Collection
                $champsArray = is_array($canevas->all_champs) ? $canevas->all_champs : $canevas->all_champs->toArray();
                $fields = $this->extractAllFields($champsArray);
            } else {
                $fields = [];
            }
        }

        return collect($fields)->firstWhere('attribut', $attribut) ?? [];
    }

    /**
     * Récupérer le canevas depuis la base de données
     */
    protected function getCanevas(): array
    {
        $documentRepository = app(DocumentRepositoryInterface::class);
        $canevas = $documentRepository->getModel()
            ->where('type', 'formulaire')
            ->whereHas('categorie', fn($q) => $q->where('slug', 'checklist-mesures-adaptation-haut-risque'))
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$canevas || !$canevas->all_champs) {
            return [];
        }

        // Convertir la Collection en array
        return $canevas->all_champs->toArray();
    }

    /**
     * Récupérer le canevas depuis la base de données
     */
    protected function getChecklistSuiviPrefaisabilite(): array
    {
        $documentRepository = app(DocumentRepositoryInterface::class);
        $canevas = $documentRepository->getModel()
            ->where('type', 'formulaire')
            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-check-liste-suivi-rapport-prefaisabilite'))
            ->orderBy('created_at', 'desc')
            ->first();
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
                "checklist_suivi_rapport_prefaisabilite.{$index}.remarque",
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
                    "checklist_suivi_rapport_prefaisabilite.{$index}.explication",
                    "L'explication ne peut pas dépasser {$maxLength} caractères."
                );
            }
            return;
        }

        // Récupérer la remarque pour ce champ
        $remarque = $this->input("checklist_suivi_rapport_prefaisabilite.{$index}.remarque");
        $remarqueEstPasEncoreDisponible = $remarque === 'pas-encore-disponibles';

        // Si show_explanation est true, valider selon les règles
        if ($estSoumise) {
            // Pour submit, l'explication est obligatoire si show_explanation = true
            if ($showExplanation && $remarqueEstPasEncoreDisponible) {
                if (empty($explication)) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_prefaisabilite.{$index}.explication",
                        "L'explication est obligatoire pour la soumission finale lorsque le champ d'explication est activé."
                    );
                } elseif (strlen($explication) < 10) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_prefaisabilite.{$index}.explication",
                        "L'explication doit contenir au moins 10 caractères."
                    );
                } elseif (strlen($explication) > $maxLength) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_prefaisabilite.{$index}.explication",
                        "L'explication ne peut pas dépasser {$maxLength} caractères."
                    );
                }
            }
        } else {
            // Pour draft, l'explication devient obligatoire SEULEMENT si show_explanation = true ET remarque = "pas-encore-disponibles"
            if ($showExplanation && $remarqueEstPasEncoreDisponible) {
                if (empty($explication)) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_prefaisabilite.{$index}.explication",
                        "L'explication est obligatoire lorsque la remarque est 'pas-encore-disponibles' et que le champ d'explication est activé."
                    );
                } elseif (strlen($explication) < 10) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_prefaisabilite.{$index}.explication",
                        "L'explication doit contenir au moins 10 caractères."
                    );
                } elseif (strlen($explication) > $maxLength) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_prefaisabilite.{$index}.explication",
                        "L'explication ne peut pas dépasser {$maxLength} caractères."
                    );
                }
            } else {
                // Pour draft dans tous les autres cas, l'explication est optionnel mais doit respecter les limites si présent
                if ($explication !== null && strlen($explication) > $maxLength) {
                    $validator->errors()->add(
                        "checklist_suivi_rapport_prefaisabilite.{$index}.explication",
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
        $canevas = $this->getChecklistSuiviPrefaisabilite();

        $fieldsWithConfigs = [];
        foreach ($canevas as $field) {
            if (!empty($field['id'])) {
                $fieldsWithConfigs[$field['id']] = $field;
            }
        }

        return $fieldsWithConfigs;
    }
}
