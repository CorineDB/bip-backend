<?php

namespace Database\Seeders;

use App\Models\CategorieDocument; // Added this line
use App\Models\Document;
use App\Models\ChampSection;
use App\Models\Champ;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str; // Added this line for Str::slug

class CanevasAppreciationRapportFinaleSeeder extends Seeder
{
    protected $documentData = [
        'nom' => 'Check liste d\'appréciation des rapports d\'évaluation ex-ante',
        'slug' => 'canevas-appreciation-rapport-finale',
        'description' => 'Check liste standardisée pour l\'appréciation et l\'évaluation des rapports d\'évaluation ex-ante (faisabilité)',
        'type' => 'checklist',
        'evaluation_configs' => [
            'results' => [
                [
                    'value' => 'passe',
                    'label' => 'Rapport validé',
                    'statut_suivant' => 'valide',
                    'message' => 'Le rapport d\'évaluation ex-ante répond à tous les critères requis',
                ],
                [
                    'value' => 'non_accepte',
                    'label' => 'Rapport non validé',
                    'statut_suivant' => 'rejete',
                    'message' => 'Le rapport d\'évaluation ex-ante ne répond pas aux critères minimums',
                ],
            ],
            'rules' => [
                'reference' => 'Selon les critères d\'appréciation des rapports d\'évaluation ex-ante définis par le cadre juridique',
                'decision_algorithm' => 'all_yes_required',
                'evaluation_required_fields' => [],
                'conditions' => [
                    [
                        'priority' => 1,
                        'name' => 'Tous les critères sont satisfaits (oui)',
                        'appreciations_concernees' => ['oui'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count_oui',
                            'operator' => '==',
                            'value_field' => 'total_questions',
                        ],
                        'result' => 'passe',
                        'message' => 'Tous les critères d\'appréciation sont satisfaits. Le rapport est validé.',
                        'recommandations' => [
                            'Le rapport d\'évaluation ex-ante peut être approuvé',
                            'Procéder aux étapes suivantes du processus de maturation',
                        ],
                    ],
                    [
                        'priority' => 2,
                        'name' => 'Condition par défaut',
                        'appreciations_concernees' => [],
                        'condition' => [
                            'type' => 'default',
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Au moins un critère n\'est pas satisfait. Le rapport ne peut pas être validé.',
                        'recommandations' => [
                            'Identifier les critères non satisfaits',
                            'Demander des clarifications ou compléments au consultant',
                            'Réviser le rapport pour répondre aux exigences',
                        ],
                    ],
                ],
            ],
            'guide_notation' => [
                [
                    'appreciation' => 'oui',
                    'libelle' => 'Oui',
                    'description' => 'Le critère est satisfait',
                    'couleur' => '#22c55e',
                ],
                [
                    'appreciation' => 'non',
                    'libelle' => 'Non',
                    'description' => 'Le critère n\'est pas satisfait',
                    'couleur' => '#ef4444',
                ],
            ],
            'accept_text' => 'En soumettant cette évaluation du rapport d\'évaluation ex-ante, je confirme avoir examiné tous les aspects requis de manière rigoureuse et objective. Je reconnais que mon évaluation contribue à la décision finale concernant la faisabilité du projet.',
        ],
        'forms' => [
            [
                'element_type' => 'field',
                'ordre_affichage' => 1,
                'label' => 'Le rapport a-t-il présenté une évaluation des résultats pertinents prévisibles de la réalisation des objectifs du projet ?',
                'info' => 'Vérifier si le rapport identifie clairement les résultats attendus, les indicateurs de performance, et si l\'évaluation des résultats est alignée avec les objectifs du projet. Les résultats doivent être mesurables, réalistes et pertinents.',
                'key' => 'evaluation_resultats_previsibles',
                'attribut' => 'evaluation_resultats_previsibles',
                'placeholder' => 'Évaluation de la présentation des résultats pertinents prévisibles',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Le rapport présente une évaluation claire et pertinente des résultats prévisibles',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'Le rapport ne présente pas d\'évaluation adéquate des résultats prévisibles',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 2,
                'label' => 'Le rapport est-il cohérent ? Les données sont-elles exhaustives et fiables ?',
                'info' => 'Vérifier la cohérence interne du rapport : absence de contradictions, logique d\'enchaînement des sections, qualité et exhaustivité des données (sources citées, méthodologie claire), et fiabilité des informations fournies.',
                'key' => 'coherence_exhaustivite_donnees',
                'attribut' => 'coherence_exhaustivite_donnees',
                'placeholder' => 'Évaluation de la cohérence et de la fiabilité des données',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Le rapport est cohérent, les données sont exhaustives et fiables',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'Le rapport manque de cohérence ou les données sont incomplètes',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 3,
                'label' => 'Le rapport a-t-il présenté une évaluation rationnelle de la durabilité des résultats ?',
                'info' => 'Vérifier si le rapport évalue la pérennité des résultats du projet : viabilité financière, institutionnelle, technique et environnementale. L\'analyse doit inclure les facteurs de risque et les mesures d\'atténuation.',
                'key' => 'evaluation_durabilite_resultats',
                'attribut' => 'evaluation_durabilite_resultats',
                'placeholder' => 'Évaluation de la durabilité des résultats',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Le rapport présente une évaluation rationnelle de la durabilité',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'Le rapport ne présente pas d\'évaluation adéquate de la durabilité',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 4,
                'label' => 'Les recommandations de faisabilité ou de non faisabilité émises dans le rapport sont-elles justifiées au regard des informations fournies ?',
                'info' => 'Vérifier si les conclusions et recommandations du rapport (faisable/non faisable) sont logiquement déduites des analyses présentées, supportées par des preuves et des arguments solides.',
                'key' => 'justification_recommandations',
                'attribut' => 'justification_recommandations',
                'placeholder' => 'Évaluation de la justification des recommandations',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Les recommandations sont justifiées et cohérentes',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'Les recommandations ne sont pas suffisamment justifiées',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 5,
                'label' => 'Les recommandations d\'ordre général précisent-elles les mesures nécessaires (« qui ? », « quoi ? », « où ? », « quand ? ») et ces mesures peuvent-elles être mises en œuvre ?',
                'info' => 'Vérifier si les recommandations sont opérationnelles et précises : acteurs responsables identifiés, actions concrètes définies, localisation, calendrier. Évaluer également la faisabilité pratique.',
                'key' => 'precision_mesures_correctives',
                'attribut' => 'precision_mesures_correctives',
                'placeholder' => 'Évaluation de la précision et faisabilité des mesures',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Les recommandations sont précises et réalisables',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'Les recommandations manquent de précision ou ne sont pas réalisables',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 6,
                'label' => 'Le rapport présente-t-il les coûts actualisés du projet par source de financement (coûts totaux et par activité) ?',
                'info' => 'Vérifier si le rapport présente de manière claire et détaillée les coûts : ventilation par source de financement, actualisation, décomposition par activité, modèle de financement et programmation financière.',
                'key' => 'presentation_couts_actualises',
                'attribut' => 'presentation_couts_actualises',
                'placeholder' => 'Évaluation de la présentation des coûts',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Les coûts sont présentés de manière claire et détaillée',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'La présentation des coûts est absente ou inadéquate',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 7,
                'label' => 'Le rapport propose-t-il un système de suivi-évaluation du projet et son mécanisme de mise en œuvre efficace ?',
                'info' => 'Vérifier si le rapport présente un cadre de suivi-évaluation complet : indicateurs de performance, sources de vérification, responsabilités, fréquence de collecte, mécanismes de reporting.',
                'key' => 'systeme_suivi_evaluation',
                'attribut' => 'systeme_suivi_evaluation',
                'placeholder' => 'Évaluation du système de suivi-évaluation',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Un système de suivi-évaluation complet est proposé',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'Le système de suivi-évaluation est absent ou inadéquat',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 8,
                'label' => 'Le rapport est-il bien rédigé ? (Clarté de la langue et de la grammaire)',
                'info' => 'Vérifier la qualité rédactionnelle : langue claire, grammaire correcte, structure logique, absence de fautes, style professionnel.',
                'key' => 'qualite_redaction',
                'attribut' => 'qualite_redaction',
                'placeholder' => 'Évaluation de la qualité de la rédaction',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Le rapport est bien rédigé avec une langue et une grammaire correctes',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'Le rapport présente des erreurs de langue qui nuisent à sa compréhension',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 9,
                'label' => 'Toutes les annexes nécessaires ont-elles été insérées ?',
                'info' => 'Vérifier la présence de toutes les annexes requises : documents justificatifs, tableaux détaillés, cartes, plans, études complémentaires, termes de référence.',
                'key' => 'presence_annexes',
                'attribut' => 'presence_annexes',
                'placeholder' => 'Évaluation de la présence des annexes',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Toutes les annexes nécessaires sont présentes',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'Les annexes sont absentes ou incomplètes',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ],
            [
                'element_type' => 'field',
                'ordre_affichage' => 10,
                'label' => 'Tous les aspects de l\'analyse de faisabilité mentionnés dans les TDR/cahier de charge ont-ils été abordés de manière adéquate ?',
                'info' => 'Vérifier si le rapport couvre tous les aspects des termes de référence avec une profondeur d\'analyse suffisante.',
                'key' => 'conformite_tdr',
                'attribut' => 'conformite_tdr',
                'placeholder' => 'Évaluation de la conformité aux TDR',
                'is_required' => true,
                'default_value' => NULL,
                'isEvaluated' => true,
                'type_champ' => 'radio',
                'meta_options' => [
                    'configs' => [
                        'options' => [
                            [
                                'label' => 'Oui',
                                'value' => 'oui',
                                'description' => 'Tous les aspects des TDR sont abordés de manière adéquate',
                            ],
                            [
                                'label' => 'Non',
                                'value' => 'non',
                                'description' => 'Des aspects des TDR ne sont pas abordés ou sont superficiels',
                            ],
                        ],
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => [],
                    ],
                    'validations_rules' => [
                        'in' => ['non', 'oui'],
                    ],
                ],
            ]
        ]
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Récupérer ou créer la catégorie de document pour l\'appréciation des rapports finaux
            $categorieDocument = CategorieDocument::where('slug', $this->documentData['slug'])->first();

            if (!$categorieDocument) {
                $categorieDocument = CategorieDocument::create([
                    'slug' => $this->documentData['slug'],
                    'nom' => $this->documentData['nom'],
                    "description" => $this->documentData['description'],
                    "format" => "checklist"
                ]);
            }

            // Extraire les données relationnelles avant création
            $formsData = $this->documentData['forms'] ?? [];

            // Nettoyer les données du document principal
            $documentData = collect($this->documentData)->except(['forms', 'champs', 'id'])->toArray();

            $documentData = array_merge($documentData, [
                "categorieId" => $categorieDocument->id
            ]);

            // Créer ou récupérer le document principal par nom
            $document = Document::updateOrCreate([
                'nom' => $documentData['nom']
            ], $documentData);

            $this->command->info('Document "' . $document->nom . '" created or updated successfully.');

            // Traiter les sections et leurs éléments
            if (!empty($formsData)) {
                foreach ($formsData as $elementData) {
                    $this->createElementRecursive($elementData, $document, null);
                }
            }

            DB::commit();

            $this->command->info('SeederCanevasAppreciationRapportFinaleSeeder exécuté avec succès!');
            $this->command->info('Document ID: ' . $document->id);
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Erreur lors du seeding: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Créer un élément (section ou champ) de manière récursive
     */
    private function createElementRecursive(array $elementData, $document, $parentSection = null): void
    {
        if ($elementData['element_type'] === 'section') {
            $this->createSection($elementData, $document, $parentSection);
        } elseif ($elementData['element_type'] === 'field') {
            $this->createChamp($elementData, $document, $parentSection);
        }
    }

    /**
     * Créer une section avec ses éléments enfants
     */
    private function createSection(array $sectionData, $document, $parentSection = null): void
    {
        $sectionAttributes = [
            'intitule' => $sectionData['intitule'], // Use 'intitule' from sectionData
            'slug' => Str::slug($sectionData['key']), // Generate slug from 'key'
            'description' => $sectionData['description'] ?? null,
            'documentId' => $document->id,
            'parentSectionId' => $parentSection ? $parentSection->id : null,
            'ordre_affichage' => $sectionData['ordre_affichage'],
            'type' => $sectionData['type'] ?? 'formulaire', // Added type
        ];

        // Créer la section en utilisant intitule et documentId pour l\'unicité
        $section = $document->sections()->updateOrCreate([
            'slug' => Str::slug($sectionData['key']), // Use slug for uniqueness
            'documentId' => $document->id
        ], $sectionAttributes);

        // Traiter les éléments enfants de la section
        if (isset($sectionData['elements']) && !empty($sectionData['elements'])) {
            foreach ($sectionData['elements'] as $childElement) {
                $this->createElementRecursive($childElement, $document, $section);
            }
        }
    }

    /**
     * Créer un champ avec validation des données
     */
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

        // Créer le champ en utilisant la contrainte d\'unicité complète
        Champ::updateOrCreate([
            'attribut' => $champData['attribut'],
            'sectionId' => $parentSection ? $parentSection->id : null,
            'documentId' => $document->id
        ], $champAttributes);
    }
}
