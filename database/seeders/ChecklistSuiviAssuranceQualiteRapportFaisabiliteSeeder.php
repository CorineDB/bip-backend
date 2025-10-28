<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistSuiviAssuranceQualiteRapportFaisabiliteSeeder extends Seeder
{
    protected $documentData = [
        'nom' => 'Check liste de suivi pour l\'assurance qualité du rapport d\'étude de faisabilité.',
        'slug' => 'canevas-check-liste-suivi-assurance-qualite-rapport-etude-faisabilite',
        'description' => 'Check liste de suivi pour l\'assurance qualité du rapport d\'étude de faisabilités',
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
            ],
        ],
        'forms' => [
            [
                'element_type' => 'section',
                'ordre_affichage' => 1,
                'key' => 'section-cadre-physique',
                'intitule' => 'Cadre Physique du projet',
                'description' => 'Cette section décrit les fondements physiques du projet, ses éléments géographiques, son origine et son secteur d’activités.',
                'type' => 'formulaire',
                'elements' => [
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 1,
                        'label' => 'Définition du projet.',
                        'info' => '',
                        'key' => 'definition_projet',
                        'attribut' => 'definition_projet',
                        'placeholder' => 'La définition du projet est-elle disponible ?',
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
                        'label' => 'Genèse du projet',
                        'info' => '',
                        'key' => 'genese_projet',
                        'attribut' => 'genese_projet',
                        'placeholder' => 'La genèse du projet est-elle documentée ?',
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
                        'label' => 'Dossier de Formulation',
                        'info' => '',
                        'key' => 'dossier_formulation',
                        'attribut' => 'dossier_formulation',
                        'placeholder' => 'Le dossier de formulation est-il disponible ?',
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
                        'label' => 'Éléments géographiques',
                        'info' => '',
                        'key' => 'elements_geographiques',
                        'attribut' => 'elements_geographiques',
                        'placeholder' => 'Les éléments géographiques sont-ils identifiés ?',
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
                        'label' => 'Éléments d’infrastructure (zones du projet]',
                        'info' => '',
                        'key' => 'elements_infrastructure',
                        'attribut' => 'elements_infrastructure',
                        'placeholder' => 'Les éléments d’infrastructure sont-ils disponibles ?',
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
                        'label' => 'Secteur d’activités du projet',
                        'info' => '',
                        'key' => 'secteur_activites',
                        'attribut' => 'secteur_activites',
                        'placeholder' => 'Le secteur d’activités du projet est-il défini ?',
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
                'key' => 'section-description-technique-projet',
                'intitule' => 'Description technique du projet',
                'description' => 'Description technique du projet',
                'type' => 'formulaire',
                'elements' => [
                    [
                        'element_type' => 'field',
                        'ordre_affichage' => 1,
                        'label' => 'Choix techniques et justification',
                        'info' => '',
                        'key' => 'choix_techniques_justification',
                        'attribut' => 'choix_techniques_justification',
                        'placeholder' => 'Les choix techniques sont-ils disponibles ?',
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
                        'label' => 'Échelonnement des réalisations',
                        'info' => '',
                        'key' => 'echelonnement_realisations',
                        'attribut' => 'echelonnement_realisations',
                        'placeholder' => 'Les informations sur l’échelonnement des réalisations sont-elles disponibles ?',
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
                                ],
                                'show_explanation' => true,
                                'explanation_min_length' => 50,
                                'explanation_placeholder' => 'Explications détaillées (optionnel]',
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
                        'label' => 'Objectifs de production',
                        'info' => '',
                        'key' => 'objectifs_production',
                        'attribut' => 'objectifs_production',
                        'placeholder' => 'Disponible ou pas ?',
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
                'slug' => 'canevas-check-liste-suivi-assurance-qualite-rapport-etude-faisabilite'
            ], [
                'nom' => "Canevas check liste suivi assurance qualite rapport etude faisabilite",
                'slug' => 'canevas-check-liste-suivi-assurance-qualite-rapport-etude-faisabilite',
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
