<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistSuiviAssuranceQualiteRapportFaisabilitePreliminaireSeeder extends Seeder

{
    protected $documentData = [
        'nom' => 'Check liste de suivi du controle qualité du rapport d\'étude de faisabilité préliminaire.',
        'slug' => 'canevas-check-liste-suivi-controle-qualite-rapport-etude-faisabilite-preliminaire',
        'description' => 'Check liste de suivi du controle qualité du rapport d\'étude de faisabilité préliminaires',
        'type' => 'checklist',
        'evaluation_configs' => [
            'guide_suivi' => [
                [
                    'option' => 'passable',
                    'libelle' => 'Passable',
                    'description' => 'Répond aux critères d\'acceptation',
                ],
                [
                    'option' => 'renvoyer',
                    'libelle' => 'Renvoyer',
                    'description' => 'Nécessite des améliorations ou éclaircissements',
                ],
                [
                    'option' => 'non_accepte',
                    'libelle' => 'Non accepté',
                    'description' => 'Répond aux critères d\'acceptation',
                ],
                [
                    'option' => 'non_applicable',
                    'libelle' => 'Non applicable',
                    'description' => 'Nécessite des améliorations ou éclaircissements',
                ],
            ],
        ],
        'forms' => [
            [
                'element_type' => 'section',
                'ordre_affichage' => 1,
                'key' => 'section-pertinence-climatique',
                'intitule' => 'Pertinence Climatique',
                'description' => '',
                'type' => 'formulaire',
                'elements' => [
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 1,
                        'label' => 'Ce projet est-il globalement conforme aux ambitions climatiques du Bénin telles qu’énoncées dans les documents de politique nationale',
                        'info' => '',
                        'key' => 'projet_conforme_climat',
                        'attribut' => 'projet_conforme_climat',
                        'placeholder' => 'Par exemple, la stratégie à faible émission de carbone et résiliente au changement climatique du Bénin, etc. ?',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => true,
                        'type_champ' => 'radio',
                        'meta_options' => [
                            'configs' => [
                                'options' => [
                                    [
                                        'label' => 'Passable',
                                        'value' => 'passable',
                                    ],
                                    [
                                        'label' => 'Renvoyer',
                                        'value' => 'renvoyer',
                                    ],
                                    [
                                        'label' => 'Non accepté',
                                        'value' => 'non_accepte',
                                    ],
                                    [
                                        'label' => 'Non applicable',
                                        'value' => 'non_applicable',
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
                                    'passable',
                                    'renvoyer',
                                    'non_accepte',
                                    'non_applicable',
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
                'element_type' => 'section',
                'ordre_affichage' => 2,
                'key' => 'section-sensibilite-climatique',
                'intitule' => 'Sensibilité Climatique',
                'description' => '',
                'type' => 'formulaire',
                'elements' => [
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 1,
                        'label' => 'L’effet net du projet sur les émissions de GES a-t-il été pris en compte dans le processus d’évaluation, le cas échéant ?',
                        'info' => '',
                        'key' => 'effet_net_projet_emission_ges',
                        'attribut' => 'effet_net_projet_emission_ges',
                        'placeholder' => 'L’effet net du projet sur les émissions de GES a-t-il été pris en compte dans le processus d’évaluation, le cas échéant ?',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => true,
                        'type_champ' => 'radio',
                        'meta_options' => [
                            'configs' => [
                                'options' => [
                                    [
                                        'label' => 'Passable',
                                        'value' => 'passable',
                                    ],
                                    [
                                        'label' => 'Renvoyer',
                                        'value' => 'renvoyer',
                                    ],
                                    [
                                        'label' => 'Non accepté',
                                        'value' => 'non_accepte',
                                    ],
                                    [
                                        'label' => 'Non applicable',
                                        'value' => 'non_applicable',
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
                                    'passable',
                                    'renvoyer',
                                    'non_accepte',
                                    'non_applicable',
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
                'element_type' => 'section',
                'ordre_affichage' => 3,
                'key' => 'section-suivi-evaluation',
                'intitule' => 'Suivi et Évaluation',
                'description' => '',
                'type' => 'formulaire',
                'elements' => [
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 1,
                        'label' => 'Existe-t-il des capacités appropriées pour mettre en œuvre les mesures d’adaptation sélectionnées si nécessaire ?',
                        'info' => '',
                        'key' => 'capacite_mis_en_oeuvre',
                        'attribut' => 'capacite_mis_en_oeuvre',
                        'placeholder' => 'Existe-t-il des capacités appropriées pour mettre en œuvre les mesures d’adaptation sélectionnées si nécessaire ?',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => true,
                        'type_champ' => 'radio',
                        'meta_options' => [
                            'configs' => [
                                'options' => [
                                    [
                                        'label' => 'Passable',
                                        'value' => 'passable',
                                    ],
                                    [
                                        'label' => 'Renvoyer',
                                        'value' => 'renvoyer',
                                    ],
                                    [
                                        'label' => 'Non accepté',
                                        'value' => 'non_accepte',
                                    ],
                                    [
                                        'label' => 'Non applicable',
                                        'value' => 'non_applicable',
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
                                    'passable',
                                    'renvoyer',
                                    'non_accepte',
                                    'non_applicable',
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
        ],
    ];

    public function run(): void
    {
        DB::beginTransaction();

        try {
            $categorieDocument = CategorieDocument::updateOrCreate([
                'slug' => 'canevas-check-liste-suivi-controle-qualite-rapport-etude-faisabilite-preliminaire'
            ], [
                'nom' => "Canevas check liste suivi controle qualite rapport etude faisabilite preliminaire",
                'slug' => 'canevas-check-liste-suivi-controle-qualite-rapport-etude-faisabilite-preliminaire',
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
