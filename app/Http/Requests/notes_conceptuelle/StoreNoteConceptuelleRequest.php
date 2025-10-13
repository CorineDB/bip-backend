<?php

namespace App\Http\Requests\notes_conceptuelle;

use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\RequiredIf;
use App\Repositories\Contracts\FichierRepositoryInterface;
use App\Models\NoteConceptuelle;

class StoreNoteConceptuelleRequest extends FormRequest
{
    const DOCUMENT_RULE = 'distinct|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $canevas = $this->getCanevas();
        $estSoumise = $this->input('est_soumise', true);
        $estMou = $this->input('est_mou', true);
        $noteId = $this->input('noteId');

        // Vérifier si noteId existe et est valide dans la table notes_conceptuelle
        $noteExists = $noteId ? DB::table('notes_conceptuelle')->where('id', $noteId)->whereNull('deleted_at')->exists() : false;

        if (empty($canevas)) {
            return [
                'est_soumise' => 'required|boolean',
                'champs' => 'required|array',
                'documents' => 'required|array',
                'documents.autres.*' => 'required|' . self::DOCUMENT_RULE,
                'documents.analyse_pre_risque_facteurs_reussite' => 'required_unless:est_soumise,1|' . self::DOCUMENT_RULE,
                'documents.etude_pre_faisabilite' => 'required_unless:est_soumise,1|' . self::DOCUMENT_RULE,
                'documents.note_conceptuelle' => 'required_unless:est_soumise,1|' . self::DOCUMENT_RULE
            ];
        }

        $defaultRules = [
            // Règles par défaut si nécessaire
        ];

        $estSoumise = $this->input('est_soumise', false);
        $champsValues = $this->input('champs', []);

        $dynamicRules = $this->buildRulesFromCanevas($canevas, $champsValues, $defaultRules, $estSoumise);

        // closure pour déterminer si un document spécifique doit être requis
        $needRequiredDocument = function (string $categorie) use ($estSoumise, $noteExists, $noteId): bool {
            if (!$estSoumise) {
                return false;
            }

            // si pas de note existante, on exige le fichier
            if (!$noteExists) {
                return true;
            }

            // si note existante => vérifier si le document est déjà uploadé
            return !$this->noteHasUploadedDocument($noteId, $categorie);
        };

        $finalRules = array_merge([
            'est_soumise' => 'required|boolean',
            'noteId' => ['sometimes', Rule::exists('notes_conceptuelle', 'id')->whereNull('deleted_at')],

            'champs' => $estSoumise ? 'required|array' : 'nullable|array|min:0',
            'documents' => $estSoumise ? 'required|array' : 'nullable|array',
            'documents.autres.*' => $estSoumise ? 'required|' . self::DOCUMENT_RULE : 'nullable|' . self::DOCUMENT_RULE,
            'documents.analyse_pre_risque_facteurs_reussite' => $estSoumise ? 'required|' . self::DOCUMENT_RULE : 'nullable|' . self::DOCUMENT_RULE,
            'documents.etude_pre_faisabilite' => $estSoumise ? 'required|' . self::DOCUMENT_RULE : 'nullable|' . self::DOCUMENT_RULE,
            'documents.note_conceptuelle' => $estSoumise ? 'required|' . self::DOCUMENT_RULE : 'nullable|' . self::DOCUMENT_RULE,
        ], $dynamicRules);

        $finalRules = array_merge([
            'est_soumise' => 'required|boolean',
            'noteId' => ['sometimes', Rule::exists('notes_conceptuelle', 'id')->whereNull('deleted_at')],
            'est_mou' => $estSoumise ? 'required|boolean' : 'nullable|boolean',

            'champs' => $estSoumise ? 'required|array' : 'nullable|array|min:0',
            'documents' => $estSoumise ? 'array' : 'nullable|array',
            'documents.autres.*' => $estSoumise ? 'required|' . self::DOCUMENT_RULE : 'nullable|' . self::DOCUMENT_RULE,

            // documents obligatoires conditionnels : obligatoires si est_soumise=true ET fichier absent sur la note existante
            'documents.analyse_pre_risque_facteurs_reussite' => [
                new RequiredIf(fn() => $needRequiredDocument('analyse_pre_risque_facteurs_reussite')),
                ...(explode('|', self::DOCUMENT_RULE))

            ],
            /*
                'documents.etude_pre_faisabilite' => [
                    new RequiredIf(fn() => $needRequiredDocument('etude_pre_faisabilite')),
                    ...(explode('|', self::DOCUMENT_RULE))
                ],
            */
            'documents.note_conceptuelle' => [
                new RequiredIf(fn() => $needRequiredDocument('note_conceptuelle')),
                ...(explode('|', self::DOCUMENT_RULE))
            ],

            // Documents requis si est_mou = true
            'documents.rapport_faisabilite_preliminaire' => [
                new RequiredIf(fn() => $estMou && !$this->noteHasUploadedDocument($noteId, 'rapport_faisabilite_preliminaire')),
                ...(explode('|', self::DOCUMENT_RULE))
            ],
            'documents.tdr_faisabilite_preliminaire' => [
                new RequiredIf(fn() => $estMou && !$this->noteHasUploadedDocument($noteId, 'tdr_faisabilite_preliminaire')),
                ...(explode('|', self::DOCUMENT_RULE))
            ],
            'documents.check_suivi_rapport' => [
                new RequiredIf(fn() => $estMou && !$this->noteHasUploadedDocument($noteId, 'check_suivi_rapport')),
                ...(explode('|', self::DOCUMENT_RULE))
            ],

            'analyse_financiere'                            => $estSoumise ? 'required|array' : 'nullable|array|min:0',
            'analyse_financiere.duree_vie'                  => 'required_unless:est_soumise,0|numeric',
            'analyse_financiere.taux_actualisation'         => 'required_unless:est_soumise,0|numeric',
            'analyse_financiere.investissement_initial'     => 'required_unless:est_soumise,0|numeric',
            'analyse_financiere.flux_tresorerie'            => 'required_unless:est_soumise,0|array|min:' . $this->input("analyse_financiere.duree_vie") ?? 1,
            'analyse_financiere.flux_tresorerie.*.t'        => 'required_unless:est_soumise,0|numeric|min:' . 1 . '|max:' . $this->input("analyse_financiere.duree_vie") ?? 1,
            'analyse_financiere.flux_tresorerie.*.CFt'      => 'required_unless:est_soumise,0|numeric|min:0'
        ], $dynamicRules);

        return $finalRules;
    }



    /**
     * Vérifie si la note (id) a déjà un fichier uploadé pour la catégorie donnée.
     * Utilise le repository fichiers. Retourne true si au moins un fichier existe.
     */
    private function noteHasUploadedDocument($noteId, string $categorie): bool
    {
        try {
            if (empty($noteId)) {
                return false;
            }

            $fichierRepo = app(FichierRepositoryInterface::class);

            // certains repository exposent getInstance() ou getModel() : essayer getInstance() puis getModel()
            $queryable = null;
            if (method_exists($fichierRepo, 'getInstance')) {
                $queryable = $fichierRepo->getInstance();
            } elseif (method_exists($fichierRepo, 'getModel')) {
                $queryable = $fichierRepo->getModel();
            } else {
                // fallback : utiliser le model direct
                $queryable = \App\Models\Fichier::query();
            }

            return $queryable->where('fichier_attachable_id', $noteId)
                ->where('fichier_attachable_type', NoteConceptuelle::class)
                ->where('categorie', $categorie)
                ->exists();
        } catch (\Exception $e) {
            \Log::warning("Erreur lors de la vérification des fichiers pour la note {$noteId}: " . $e->getMessage());
            // En cas d'erreur on considère qu'il n'y a pas de fichier uploadé (donc il sera requis)
            return false;
        }
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
            if (($el['element_type'] ?? null) === 'field') {
                $fields[] = $el;
            }
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
                ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-redaction-note-conceptuelle'))
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
            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-redaction-note-conceptuelle'))
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$canevas || !$canevas->all_champs) {
            return [];
        }

        // Convertir la Collection en array
        return $canevas->all_champs->toArray();
    }
}
