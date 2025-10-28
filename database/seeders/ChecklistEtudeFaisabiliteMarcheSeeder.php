<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistEtudeFaisabiliteMarcheSeeder extends Seeder
{
    protected $documentData = [
        'nom' => 'Check liste d\'étude de faisabilité marché',
        'slug' => 'canevas-check-liste-etude-faisabilite-marche',
        'description' => 'Check liste d\'étude de faisabilité des  marchés',
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
                'label' => 'Présentation du contexte, analyse situationnelle et définition des objectifs de l\'étude-jf',
                'info' => '',
                'key' => 'contexte_analyse',
                'attribut' => 'contexte_analyse',
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
                        'show_explanation' => true,
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
                'label' => 'Méthodologie (données et approches d’analyse]',
                'info' => '',
                'key' => 'methodologie',
                'attribut' => 'methodologie',
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
                        'show_explanation' => true,
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
                'element_type' => 'section',
                'ordre_affichage' => 3,
                'key' => 'section-analyse-marche',
                'intitule' => 'Analyse du marché',
                'description' => '',
                'type' => 'formulaire',
                'elements' => [
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 1,
                        'label' => 'Analyse de la demande',
                        'info' => '',
                        'key' => 'analyse_demande',
                        'attribut' => 'analyse_demande',
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
                                'show_explanation' => true,
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
                        'label' => 'Analyse de l’offre',
                        'info' => '',
                        'key' => 'analyse_offre',
                        'attribut' => 'analyse_offre',
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
                                'show_explanation' => true,
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
                        'label' => 'Analyse concurrentielle',
                        'info' => '',
                        'key' => 'analyse_concurrentielle',
                        'attribut' => 'analyse_concurrentielle',
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
                                'show_explanation' => true,
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
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 4,
                'label' => 'Estimation du potentiel de vente (quantité et consentement à payer]',
                'info' => '',
                'key' => 'estimation_potentiel_vente',
                'attribut' => 'estimation_potentiel_vente',
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
                        'show_explanation' => true,
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
                'label' => 'Mix-marketings',
                'info' => '',
                'key' => 'mix_marketing',
                'attribut' => 'mix_marketing',
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
                        'show_explanation' => true,
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
                'slug' => 'canevas-check-liste-etude-faisabilite-marche'
            ], [
                'nom' => "Canevas check liste etude faisabilite marche",
                'slug' => 'canevas-check-liste-etude-faisabilite-marche',
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
            'intitule' => $sectionData['label'] ?? $sectionData['intitule'] ?? 'Section sans titre',
            'slug' => $sectionData['attribut'] ?? $sectionData['key'] ?? null,
            'description' => $sectionData['description'] ?? null,
            'documentId' => $document->id,
            'parentSectionId' => $parentSection ? $parentSection->id : null,
            'ordre_affichage' => $sectionData['ordre_affichage'],
        ];

        $section = $document->sections()->updateOrCreate([
            'intitule' => $sectionData['label'] ?? $sectionData['intitule'] ?? 'Section sans titre',
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
