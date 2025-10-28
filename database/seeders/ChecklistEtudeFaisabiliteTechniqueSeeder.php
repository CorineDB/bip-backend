<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistEtudeFaisabiliteTechniqueSeeder extends Seeder
{
    protected $documentData = [
        'nom' => 'Check liste de suivi de l\'étude de faisabilité technique',
        'slug' => 'check-liste-suivi-etude-faisabilite-technique',
        'description' => 'Check liste de suivi - Etude de faisabilité techniques',
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
                'label' => 'Collecte et compilation d’informations préalables',
                'info' => '',
                'key' => 'collecte_compilation',
                'attribut' => 'collecte_compilation',
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
                'label' => 'Fixation/détermination de la capacité de production',
                'info' => '',
                'key' => 'fixation',
                'attribut' => 'fixation',
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
                'label' => 'Description des caractéristiques des matériaux et des intrants',
                'info' => '',
                'key' => 'description_caractéristiques',
                'attribut' => 'description_caractéristiques',
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
                'label' => 'Choix d’une technologie ou des du processus de fabrication ou de production des biens et services',
                'info' => '',
                'key' => 'choix_technologique',
                'attribut' => 'choix_technologique',
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
                'label' => 'Description de la machinerie et de l’équipement',
                'info' => '',
                'key' => 'description_machinerie_equipement',
                'attribut' => 'description_machinerie_equipement',
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
                'label' => 'Définition des aspects organisationnels et de la phase de mise en œuvre du projet',
                'info' => '',
                'key' => 'definition_aspects_organisationnels_phase',
                'attribut' => 'definition_aspects_organisationnels_phase',
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
                'label' => 'Description des bâtiments et infrastructures',
                'info' => '',
                'key' => 'description_batiments_infrastructures',
                'attribut' => 'description_batiments_infrastructures',
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
                'label' => 'Choix d’un site et localisation',
                'info' => '',
                'key' => 'choix_site_localisation',
                'attribut' => 'choix_site_localisation',
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
                'ordre_affichage' => 9,
                'label' => 'Estimation des coûts d’investissement et des coûts de mise en exploitation du projet',
                'info' => '',
                'key' => 'estimation_couts_investissement_mise_exploitation',
                'attribut' => 'estimation_couts_investissement_mise_exploitation',
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
                'slug' => 'canevas-check-liste-etude-faisabilite-technique'
            ], [
                'nom' => "Canevas check liste etude faisabilite technique",
                'slug' => 'canevas-check-liste-etude-faisabilite-technique',
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
