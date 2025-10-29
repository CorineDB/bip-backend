<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanvasRedactionNoteConceptuelleSeeder extends Seeder
{
    protected $documentData = [
        'nom' => 'Canevas de rédaction de la note conceptuelle',
        'slug' => 'canevas-redaction-note-conceptuelle',
        'description' => 'Formulaire de rédaction d\'une note conceptuelle de projet',
        'type' => 'formulaire',
        'forms' => [
            [
                'element_type' => 'field',
                'ordre_affichage' => 1,
                'label' => 'Contexte et justification',
                'info' => '',
                'key' => 'contexte_justification',
                'attribut' => 'contexte_justification',
                'placeholder' => 'Décrivez le contexte et la justification du projet',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => false,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 2000,
                        'min_length' => 10,
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'string' => true,
                        'required' => true,
                    ],
                ],
                'champ_standard' => false,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 2,
                'label' => 'Objectifs du projet',
                'info' => '',
                'key' => 'objectifs_projet',
                'attribut' => 'objectifs_projet',
                'placeholder' => 'Définissez les objectifs du projet',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => false,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 2000,
                        'min_length' => 10,
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'string' => true,
                        'required' => true,
                    ],
                ],
                'champ_standard' => false,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 3,
                'label' => 'Résultats attendus du projet',
                'info' => '',
                'key' => 'resultats_attendus',
                'attribut' => 'resultats_attendus',
                'placeholder' => 'Décrivez les résultats attendus',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => false,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 2000,
                        'min_length' => 10,
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'string' => true,
                        'required' => true,
                    ],
                ],
                'champ_standard' => false,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'section',
                'ordre_affichage' => 4,
                'key' => 'demarche-conduite-processus',
                'intitule' => 'Démarche de conduite du processus d \'élaboration du projet',
                'description' => 'Processus d\'élaboration du projet',
                'type' => 'formulaire',
                'elements' => [
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 1,
                        'label' => 'Démarche administrative',
                        'info' => '',
                        'key' => 'demarche_administrative',
                        'attribut' => 'demarche_administrative',
                        'placeholder' => 'Décrivez la démarche administrative',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 4,
                                'max_length' => 2000,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'string' => true,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => false,
                        'startWithNewLine' => false,
                    ],
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 2,
                        'label' => 'Démarche technique',
                        'info' => '',
                        'key' => 'demarche_technique',
                        'attribut' => 'demarche_technique',
                        'placeholder' => 'Décrivez la démarche technique',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 4,
                                'max_length' => 2000,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'string' => true,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => false,
                        'startWithNewLine' => false,
                    ],
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 3,
                        'label' => 'Parties prenantes',
                        'info' => '',
                        'key' => 'parties_prenantes',
                        'attribut' => 'parties_prenantes',
                        'placeholder' => 'Identifiez les parties prenantes',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 4,
                                'max_length' => 2000,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'string' => true,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => false,
                        'startWithNewLine' => false,
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 5,
                'label' => 'Les livrables du processus d\'élaboration du projet',
                'info' => '',
                'key' => 'livrables_processus',
                'attribut' => 'livrables_processus',
                'placeholder' => 'Listez les livrables attendus',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => false,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 2000,
                        'min_length' => 10,
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'string' => true,
                        'required' => true,
                    ],
                ],
                'champ_standard' => false,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 6,
                'label' => 'Cohérence du projet avec le PAG ou la stratégie sectorielle',
                'info' => '',
                'key' => 'coherence_strategique',
                'attribut' => 'coherence_strategique',
                'placeholder' => '',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => false,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 2000,
                        'min_length' => 10,
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'string' => true,
                        'required' => true,
                    ],
                ],
                'champ_standard' => false,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 7,
                'label' => 'Pilotage et gouvernance du projet',
                'info' => '',
                'key' => 'pilotage_gouvernance',
                'attribut' => 'pilotage_gouvernance',
                'placeholder' => 'Décrivez le pilotage et la gouvernance',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => false,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 2000,
                        'min_length' => 10,
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'string' => true,
                        'required' => true,
                    ],
                ],
                'champ_standard' => false,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 8,
                'label' => 'Chronogramme du processus',
                'info' => '',
                'key' => 'chronogramme_processus',
                'attribut' => 'chronogramme_processus',
                'placeholder' => 'Décrivez le chronogramme',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => false,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 2000,
                        'min_length' => 10,
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'string' => true,
                        'required' => true,
                    ],
                ],
                'champ_standard' => false,
                'startWithNewLine' => false,
            ],
            [
                'element_type' => 'section',
                'ordre_affichage' => 9,
                'key' => 'budget-et-financement',
                'intitule' => 'Budget et sources de financement du projet',
                'description' => 'Aspects financiers du projet',
                'type' => 'formulaire',
                'elements' => [
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 1,
                        'label' => 'Budget détaillé du processus',
                        'info' => '',
                        'key' => 'budget_detaille',
                        'attribut' => 'budget_detaille',
                        'placeholder' => 'Présentez le budget détaillé',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 4,
                                'max_length' => 2000,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'string' => true,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => false,
                        'startWithNewLine' => false,
                    ],
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 2,
                        'label' => 'Coût estimatif du projet',
                        'info' => 'Coût estimatif du projet',
                        'key' => 'cout_estimatif_projet',
                        'attribut' => 'cout_estimatif_projet',
                        'placeholder' => 'Indiquez le coût estimatif global',
                        'is_required' => true,
                        'default_value' => 0,
                        'isEvaluated' => false,
                        'type_champ' => 'number',
                        'meta_options' => [
                            'configs' => [
                                'step' => 1,
                                'decimal_places' => 0,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'numeric' => true,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => false,
                        'startWithNewLine' => false,
                    ],
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 3,
                        'label' => 'Sources de financement',
                        'info' => 'Sélectionnez les sources de financement spécifiques',
                        'key' => 'sources_financement',
                        'attribut' => 'sources_financement',
                        'placeholder' => 'Choisissez les sources de financement',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 4,
                                'max_length' => 2000,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'string' => true,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => false,
                        'startWithNewLine' => false,
                    ],
                ],
            ],
        ],
    ];

    public function run(): void
    {
        DB::beginTransaction();

        try {
            $categorieDocument = CategorieDocument::updateOrCreate([
                'slug' => 'canevas-redaction-note-conceptuelle'
            ], [
                'nom' => "Canevas redaction note conceptuelle",
                'slug' => 'canevas-redaction-note-conceptuelle',
                'format' => 'formulaire'
            ]);

            //$categorieDocument->documents()->first()->forceDelete();

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
            'intitule' => $sectionData['intitule'] ?? $sectionData['label'] ?? 'Section sans titre',
            'slug' => $sectionData['key'] ?? $sectionData['attribut'] ?? null,
            'description' => $sectionData['description'] ?? null,
            'documentId' => $document->id,
            'parentSectionId' => $parentSection ? $parentSection->id : null,
            'ordre_affichage' => $sectionData['ordre_affichage'],
        ];

        $section = $document->sections()->updateOrCreate([
            'intitule' => $sectionData['intitule'] ?? $sectionData['label'] ?? 'Section sans titre',
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
