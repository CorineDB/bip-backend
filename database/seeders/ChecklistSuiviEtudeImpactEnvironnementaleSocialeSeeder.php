<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistSuiviEtudeImpactEnvironnementaleSocialeSeeder extends Seeder
{
    protected $documentData = [
  'nom' => 'Check liste de suivi de l\'étude d\'analyse d’impact environnementale et sociale',
  'slug' => 'canevas-appreciation-tdr-1',
  'description' => 'Check liste de suivi de l\'étude d\'analyse d’impact environnementale et sociales',
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
      'element_type' => 'section',
      'ordre_affichage' => 1,
      'key' => 'section-prise-connaissance-environnement',
      'intitule' => 'PRISE DE CONNAISSANCE DE L’ENVIRONNEMENT DU PROJET',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Comprendre l’environnement comme cible de l’étude',
          'info' => '',
          'key' => 'comprendre_environnement',
          'attribut' => 'comprendre_environnement',
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
              'conditions' => [
              ],
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
          'label' => 'Préciser les changements prévus dans l’environnement du projet',
          'info' => '',
          'key' => 'changement_prevus_environnement',
          'attribut' => 'changement_prevus_environnement',
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
              'conditions' => [
              ],
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
          'label' => 'Décrire l’environnement du projet',
          'info' => '',
          'key' => 'decrire_environnement',
          'attribut' => 'decrire_environnement',
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
              'conditions' => [
              ],
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
          'label' => 'Examiner l’état actuel de l’environnement du projet',
          'info' => '',
          'key' => 'examiner_etat_actuel_environnement',
          'attribut' => 'examiner_etat_actuel_environnement',
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
              'conditions' => [
              ],
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
      'element_type' => 'section',
      'ordre_affichage' => 2,
      'key' => 'section-analyse-impact-environnementaux-sociaux',
      'intitule' => 'ANALYSE DES IMPACTS ENVIRONNEMENTAUX ET SOCIAUX DU PROJET',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Décrire les impacts connus ou probables du projet',
          'info' => '',
          'key' => 'decrire_impact',
          'attribut' => 'decrire_impact',
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
              'conditions' => [
              ],
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
          'label' => 'Évaluer les impacts du projet',
          'info' => '',
          'key' => 'evaluer_impact_projet',
          'attribut' => 'evaluer_impact_projet',
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
              'conditions' => [
              ],
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
      'element_type' => 'section',
      'ordre_affichage' => 3,
      'key' => 'section-definition-strategie-gestion-impact',
      'intitule' => 'DÉFINITION DES STRATÉGIES DE GESTION DES IMPACTS',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Identifier les stratégies pour gérer adéquatement les impacts du projet',
          'info' => '',
          'key' => 'identifier_strategies',
          'attribut' => 'identifier_strategies',
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
              'conditions' => [
              ],
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
          'label' => 'Piloter la gestion d’impacts du projet',
          'info' => '',
          'key' => 'piloter_gestion_impact',
          'attribut' => 'piloter_gestion_impact',
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
              'conditions' => [
              ],
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
          'label' => 'Élaborer et déployer une stratégie de communication avec la méthode du QQOQCCP',
          'info' => '',
          'key' => 'elaborer_deployer_strategie_com',
          'attribut' => 'elaborer_deployer_strategie_com',
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
              'conditions' => [
              ],
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
      'element_type' => 'section',
      'ordre_affichage' => 4,
      'key' => 'section-evaluer-cout-etude-faisabilite-environnementale-sociale',
      'intitule' => 'EVALUER LES COÛTS DE L’ÉTUDE DE FAISABILITÉ ENVIRONNEMENTALE ET SOCIALE',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Atténuation des impacts, Subvention, Exonérations',
          'info' => '',
          'key' => 'attenuation_impacts_subvention_exoneration',
          'attribut' => 'attenuation_impacts_subvention_exoneration',
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
              'conditions' => [
              ],
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
          'label' => 'Évaluer la somme des dépenses liées aux aspects sociaux et environnementaux et à leur gestion',
          'info' => '',
          'key' => 'evaluer_somme_depenses',
          'attribut' => 'evaluer_somme_depenses',
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
              'conditions' => [
              ],
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
  ],
];

    public function run(): void
    {
        DB::beginTransaction();

        try {
            $categorieDocument = CategorieDocument::updateOrCreate([
                'slug' => 'canevas-check-liste-de-suivi-etude-analyse-impact-environnementale-sociale'
            ], [
                'nom' => "Canevas check liste de suivi etude analyse impact environnementale sociale",
                'slug' => 'canevas-check-liste-de-suivi-etude-analyse-impact-environnementale-sociale',
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
