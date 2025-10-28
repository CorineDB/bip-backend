<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistEtudeFaisabiliteEconomiqueSeeder extends Seeder
{
    protected $documentData = [
  'nom' => 'Check liste de suivi de l\'étude d\'analyse de la faisabilité économique',
  'slug' => 'check-liste-suivi-etude-faisabilite-economique',
  'description' => 'Check liste de suivi de l\'étude d\'analyse de la faisabilité économiques',
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
      'label' => 'Identification des différences significatives entre les situations économiques « sans » et « avec » le projet',
      'info' => '',
      'key' => 'identification',
      'attribut' => 'identification',
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
      'label' => 'Mesure des dites différences (mesure des perturbations et changements]',
      'info' => '',
      'key' => 'mesure',
      'attribut' => 'mesure',
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
      'label' => 'Examen des conditions dans lesquelles ces différences reconnues et chiffrées peuvent être considérées comme des avantages ou des inconvénients pour les agents économiques concernés (appréciation des coûts et des avantages]',
      'info' => '',
      'key' => 'examen_condition',
      'attribut' => 'examen_condition',
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
      'label' => 'Appréciation du rapport entre ces avantages et ces inconvénients et coûts (autrement dit, dans quelle mesure les avantages justifient-ils les inconvénients dont il faudra payer le prix].',
      'info' => '',
      'key' => 'appreciation_rapport',
      'attribut' => 'appreciation_rapport',
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
];

    public function run(): void
    {
        DB::beginTransaction();

        try {
            $categorieDocument = CategorieDocument::updateOrCreate([
                'slug' => 'canevas-check-liste-etude-faisabilite-economique'
            ], [
                'nom' => "Canevas check liste etude faisabilite economique",
                'slug' => 'canevas-check-liste-etude-faisabilite-economique',
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
