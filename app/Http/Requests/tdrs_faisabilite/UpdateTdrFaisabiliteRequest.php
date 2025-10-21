<?php

namespace App\Http\Requests\tdrs_faisabilite;

use Illuminate\Foundation\Http\FormRequest;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class UpdateTdrFaisabiliteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'est_soumise'           => 'required|boolean',
            'champs'                => 'nullable|array|min:0',
            'termes_de_reference'   => 'required|array',
            'termes_de_reference.*' => 'required|distinct|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx'
        ];

        $defaultRules = [
            // Règles par défaut si nécessaire
        ];

        $estSoumise = $this->input('est_soumise', false);
        $champsValues = $this->input('champs', []);

        $dynamicRules = $this->buildRulesFromCanevas($canevas, $champsValues, $defaultRules, $estSoumise);

        $finalRules = array_merge([
            'est_soumise' => 'required|boolean',
            'champs'                => 'nullable|array|min:0',
            'termes_de_reference' => 'required|array',
            'termes_de_reference.*' => 'distinct|file|max:2048|mimes:pdf,jpg,jpeg,png,doc,docx',
        ], $dynamicRules);

        return $finalRules;
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
                        $scalarValues = array_filter($value, function($v) {
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
                ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-tdr-faisabilite'))
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
            ->whereHas('categorie', fn($q) => $q->where('slug', 'canevas-tdr-faisabilite'))
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$canevas || !$canevas->all_champs) {
            return [];
        }

        // Convertir la Collection en array
        return $canevas->all_champs->toArray();
    }
}
