<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistSuiviAnalyseFaisabiliteFinanciereSeeder extends Seeder
{
    protected $documentData = [
        'nom' => 'Check liste de suivi d\'analyse de la faisabilité financières',
        'slug' => 'check-liste-de-suivi-analyse-de-faisabilite-financiere',
        'description' => 'Check liste de suivi d\'analyse de la faisabilité financière',
        'type' => 'checklist',
        'evaluation_configs' => [
            'guide_suivi' => [
                [
                    'option' => 'disponible',
                    'libelle' => 'Disponible',
                    'description' => 'Répond aux critères d\'acceptation',
                ],
                [
                    'option' => 'pas-encore-disponibles',
                    'libelle' => 'Pas encore disponibles',
                    'description' => 'Nécessite des améliorations ou éclaircissements',
                ],
                [
                    'option' => 'pertinent',
                    'libelle' => 'Pertinent',
                    'description' => 'Nécessite des améliorations ou éclaircissements',
                ],
                [
                    'option' => 'non-pertinent',
                    'libelle' => 'Non Pertinent',
                    'description' => 'Nécessite des améliorations ou éclaircissements',
                ],
            ],
        ],
        'forms' => [
            [
                'element_type' => 'field',
                'ordre_affichage' => 1,
                'label' => 'Evaluer le coût du projet',
                'info' => '',
                'key' => 'evaluer_cout',
                'attribut' => 'evaluer_cout',
                'placeholder' => '',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Disponible',
                                'value' => 'disponible',
                            ],
                            [
                                'label' => 'Pas encore disponibles',
                                'value' => 'pas-encore-disponibles',
                            ],
                            [
                                'label' => 'Pertinent',
                                'value' => 'pertinent',
                            ],
                            [
                                'label' => 'Non Pertinent',
                                'value' => 'non-pertinent',
                            ],
                        ],
                        'show_explanation' => false,
                        'explanation_min_length' => 50,
                        'explanation_placeholder' => 'Fournissez des détails ou justifications (optionnel]',
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => [
                            'disponible',
                            'pas-encore-disponibles',
                            'pertinent',
                            'non-pertinent',
                        ],
                        'string' => true,
                        'required' => true,
                        'explanation_validation' => [
                            'min' => 50,
                            'string' => true,
                            'required' => false,
                        ],
                    ],
                ],
                'champ_standard' => true,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 2,
                'label' => 'Établir les Comptes de résultats prévisionnels',
                'info' => '',
                'key' => 'etablir_comptes_resultats_previsionnels',
                'attribut' => 'etablir_comptes_resultats_previsionnels',
                'placeholder' => '',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Disponible',
                                'value' => 'disponible',
                            ],
                            [
                                'label' => 'Pas encore disponibles',
                                'value' => 'pas-encore-disponibles',
                            ],
                            [
                                'label' => 'Pertinent',
                                'value' => 'pertinent',
                            ],
                            [
                                'label' => 'Non Pertinent',
                                'value' => 'non-pertinent',
                            ],
                        ],
                        'show_explanation' => false,
                        'explanation_min_length' => 50,
                        'explanation_placeholder' => 'Fournissez des détails ou justifications (optionnel]',
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => [
                            'disponible',
                            'pas-encore-disponibles',
                            'pertinent',
                            'non-pertinent',
                        ],
                        'string' => true,
                        'required' => true,
                        'explanation_validation' => [
                            'min' => 50,
                            'string' => true,
                            'required' => false,
                        ],
                    ],
                ],
                'champ_standard' => true,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 3,
                'label' => 'Élaborer le plan de financement',
                'info' => '',
                'key' => 'elaborer_plan_financement',
                'attribut' => 'elaborer_plan_financement',
                'placeholder' => '',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Disponible',
                                'value' => 'disponible',
                            ],
                            [
                                'label' => 'Pas encore disponibles',
                                'value' => 'pas-encore-disponibles',
                            ],
                            [
                                'label' => 'Pertinent',
                                'value' => 'pertinent',
                            ],
                            [
                                'label' => 'Non Pertinent',
                                'value' => 'non-pertinent',
                            ],
                        ],
                        'show_explanation' => false,
                        'explanation_min_length' => 50,
                        'explanation_placeholder' => 'Fournissez des détails ou justifications (optionnel]',
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => [
                            'disponible',
                            'pas-encore-disponibles',
                            'pertinent',
                            'non-pertinent',
                        ],
                        'string' => true,
                        'required' => true,
                        'explanation_validation' => [
                            'min' => 50,
                            'string' => true,
                            'required' => false,
                        ],
                    ],
                ],
                'champ_standard' => true,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 4,
                'label' => 'Élaborer le plan de trésorerie',
                'info' => '',
                'key' => 'elaborer_plan_tresorerie',
                'attribut' => 'elaborer_plan_tresorerie',
                'placeholder' => '',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Disponible',
                                'value' => 'disponible',
                            ],
                            [
                                'label' => 'Pas encore disponibles',
                                'value' => 'pas-encore-disponibles',
                            ],
                            [
                                'label' => 'Pertinent',
                                'value' => 'pertinent',
                            ],
                            [
                                'label' => 'Non Pertinent',
                                'value' => 'non-pertinent',
                            ],
                        ],
                        'show_explanation' => false,
                        'explanation_min_length' => 50,
                        'explanation_placeholder' => 'Fournissez des détails ou justifications (optionnel]',
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => [
                            'disponible',
                            'pas-encore-disponibles',
                            'pertinent',
                            'non-pertinent',
                        ],
                        'string' => true,
                        'required' => true,
                        'explanation_validation' => [
                            'min' => 50,
                            'string' => true,
                            'required' => false,
                        ],
                    ],
                ],
                'champ_standard' => true,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 5,
                'label' => 'Déterminer la VAN et le TRI',
                'info' => '',
                'key' => 'determiner_van_tri',
                'attribut' => 'determiner_van_tri',
                'placeholder' => '',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Disponible',
                                'value' => 'disponible',
                            ],
                            [
                                'label' => 'Pas encore disponibles',
                                'value' => 'pas-encore-disponibles',
                            ],
                            [
                                'label' => 'Pertinent',
                                'value' => 'pertinent',
                            ],
                            [
                                'label' => 'Non Pertinent',
                                'value' => 'non-pertinent',
                            ],
                        ],
                        'show_explanation' => false,
                        'explanation_min_length' => 50,
                        'explanation_placeholder' => 'Fournissez des détails ou justifications (optionnel]',
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => [
                            'disponible',
                            'pas-encore-disponibles',
                            'pertinent',
                            'non-pertinent',
                        ],
                        'string' => true,
                        'required' => true,
                        'explanation_validation' => [
                            'min' => 50,
                            'string' => true,
                            'required' => false,
                        ],
                    ],
                ],
                'champ_standard' => true,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 6,
                'label' => 'Calculer le délai de récupération',
                'info' => '',
                'key' => 'calculer_delai_recuperation',
                'attribut' => 'calculer_delai_recuperation',
                'placeholder' => '',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Disponible',
                                'value' => 'disponible',
                            ],
                            [
                                'label' => 'Pas encore disponibles',
                                'value' => 'pas-encore-disponibles',
                            ],
                            [
                                'label' => 'Pertinent',
                                'value' => 'pertinent',
                            ],
                            [
                                'label' => 'Non Pertinent',
                                'value' => 'non-pertinent',
                            ],
                        ],
                        'show_explanation' => false,
                        'explanation_min_length' => 50,
                        'explanation_placeholder' => 'Fournissez des détails ou justifications (optionnel]',
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => [
                            'disponible',
                            'pas-encore-disponibles',
                            'pertinent',
                            'non-pertinent',
                        ],
                        'string' => true,
                        'required' => true,
                        'explanation_validation' => [
                            'min' => 50,
                            'string' => true,
                            'required' => false,
                        ],
                    ],
                ],
                'champ_standard' => true,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 7,
                'label' => 'Procéder au test de sensibilité',
                'info' => '',
                'key' => 'proceder_test_sensibilite',
                'attribut' => 'proceder_test_sensibilite',
                'placeholder' => '',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Disponible',
                                'value' => 'disponible',
                            ],
                            [
                                'label' => 'Pas encore disponibles',
                                'value' => 'pas-encore-disponibles',
                            ],
                            [
                                'label' => 'Pertinent',
                                'value' => 'pertinent',
                            ],
                            [
                                'label' => 'Non Pertinent',
                                'value' => 'non-pertinent',
                            ],
                        ],
                        'show_explanation' => false,
                        'explanation_min_length' => 50,
                        'explanation_placeholder' => 'Fournissez des détails ou justifications (optionnel]',
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => [
                            'disponible',
                            'pas-encore-disponibles',
                            'pertinent',
                            'non-pertinent',
                        ],
                        'string' => true,
                        'required' => true,
                        'explanation_validation' => [
                            'min' => 50,
                            'string' => true,
                            'required' => false,
                        ],
                    ],
                ],
                'champ_standard' => true,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 8,
                'label' => 'Déterminers le seuil de rentabilité',
                'info' => '',
                'key' => 'determiner_seuil_rentabilite',
                'attribut' => 'determiner_seuil_rentabilite',
                'placeholder' => '',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Disponible',
                                'value' => 'disponible',
                            ],
                            [
                                'label' => 'Pas encore disponibles',
                                'value' => 'pas-encore-disponibles',
                            ],
                            [
                                'label' => 'Pertinent',
                                'value' => 'pertinent',
                            ],
                            [
                                'label' => 'Non Pertinent',
                                'value' => 'non-pertinent',
                            ],
                        ],
                        'show_explanation' => false,
                        'explanation_min_length' => 50,
                        'explanation_placeholder' => 'Fournissez des détails ou justifications (optionnel]',
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => [
                            'disponible',
                            'pas-encore-disponibles',
                            'pertinent',
                            'non-pertinent',
                        ],
                        'string' => true,
                        'required' => true,
                        'explanation_validation' => [
                            'min' => 50,
                            'string' => true,
                            'required' => false,
                        ],
                    ],
                ],
                'champ_standard' => true,
                'startWithNewLine' => false,
            ],
        ],
    ];

    public function run(): void
    {
        DB::beginTransaction();

        try {
            $categorieDocument = CategorieDocument::updateOrCreate([
                'slug' => 'canevas-check-liste-de-suivi-analyse-de-faisabilite-financiere'
            ], [
                'nom' => "Canevas check liste de suivi analyse de faisabilite financiere",
                'slug' => 'canevas-check-liste-de-suivi-analyse-de-faisabilite-financiere',
                'format' => 'checklist'
            ]);

            $formsData = $this->documentData['forms'] ?? [];
            $documentData = collect($this->documentData)->except(['forms', 'champs', 'id'])->toArray();
            $documentData = array_merge($documentData, ["categorieId" => $categorieDocument->id]);

            $document = Document::updateOrCreate(['nom' => $documentData['nom']], $documentData);

            if (!empty($formsData)) {
                foreach ($formsData as $elementData) {
                    $this->createElementRecursive($elementData, $document, null);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function createElementRecursive(array $elementData, $document, $parentSection = null): void
    {
        if ($elementData['element_type'] === 'section') {
            $this->createSection($elementData, $document, $parentSection);
        } elseif ($elementData['element_type'] === 'field') {
            $this->createChamp($elementData, $document, $parentSection);
        }
    }

    private function createSection(array $sectionData, $document, $parentSection = null): void
    {
        $sectionAttributes = [
            'intitule' => $sectionData['label'],
            'slug' => $sectionData['attribut'] ?? null,
            'description' => $sectionData['description'] ?? null,
            'documentId' => $document->id,
            'parentSectionId' => $parentSection ? $parentSection->id : null,
            'ordre_affichage' => $sectionData['ordre_affichage'],
        ];

        $section = $document->sections()->updateOrCreate([
            'intitule' => $sectionData['label'],
            'documentId' => $document->id
        ], $sectionAttributes);

        if (isset($sectionData['elements']) && !empty($sectionData['elements'])) {
            foreach ($sectionData['elements'] as $childElement) {
                $this->createElementRecursive($childElement, $document, $section);
            }
        }
    }

    private function createChamp(array $champData, $document, $parentSection = null): void
    {
        $champAttributes = [
            'label' => $champData['label'],
            'info' => $champData['info'] ?? null,
            'attribut' => $champData['attribut'] ?? null,
            'placeholder' => $champData['placeholder'] ?? null,
            'is_required' => $champData['is_required'] ?? false,
            'champ_standard' => $champData['champ_standard'] ?? false,
            'isEvaluated' => $champData['isEvaluated'] ?? false,
            'default_value' => $champData['default_value'] ?? null,
            'ordre_affichage' => $champData['ordre_affichage'],
            'type_champ' => $champData['type_champ'],
            'meta_options' => $champData['meta_options'] ?? [],
            'startWithNewLine' => $champData['startWithNewLine'] ?? false,
            'documentId' => $document->id,
            'sectionId' => $parentSection ? $parentSection->id : null
        ];

        \App\Models\Champ::updateOrCreate([
            'attribut' => $champData['attribut'],
            'sectionId' => $parentSection ? $parentSection->id : null,
            'documentId' => $document->id
        ], $champAttributes);
    }
}
