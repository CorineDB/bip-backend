<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EvaluationConfigsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // 1. Configuration pour Note Conceptuelle
            $this->seedNoteConceptuelleConfig();

            // 2. Configuration pour TDR Préfaisabilité (SFD-011)
            $this->seedTdrPrefaisabiliteConfig();

            // 3. Configuration pour TDR Faisabilité (SFD-015)
            $this->seedTdrFaisabiliteConfig();

            // 4. Configuration pour Contrôle Qualité
            $this->seedControleQualiteConfig();

            DB::commit();

            $this->command->info('✅ Configurations d\'évaluation créées avec succès !');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erreur : ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Configuration pour Note Conceptuelle
     */
    protected function seedNoteConceptuelleConfig(): void
    {
        $canevas = Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-appreciation-note-conceptuelle');
        })->where('type', 'checklist')->first();

        if (!$canevas) {
            $this->command->warn('⚠️  Canevas Note Conceptuelle non trouvé');
            return;
        }

        // Récupérer la config existante pour ne pas écraser les structures existantes
        $existingConfig = $canevas->evaluation_configs ?? [];

        $config = array_merge($existingConfig, [
            // On garde guide_notation existant s'il existe déjà
            'guide_notation' => $existingConfig['guide_notation'] ?? [
                [
                    'appreciation' => 'passe',
                    'libelle' => 'Passé',
                    'description' => 'Le critère est satisfaisant',
                    'couleur' => 'success'
                ],
                [
                    'appreciation' => 'retour',
                    'libelle' => 'Retour',
                    'description' => 'Nécessite des améliorations',
                    'couleur' => 'warning'
                ],
                [
                    'appreciation' => 'non_accepte',
                    'libelle' => 'Non accepté',
                    'description' => 'Le critère n\'est pas acceptable',
                    'couleur' => 'danger'
                ]
            ],

            'results' => [
                [
                    'value' => 'passe',
                    'label' => 'Passé',
                    'statut_suivant' => 'VALIDATION_PROFIL',
                    'message' => 'La présélection a été un succès (passes reçues dans toutes les questions)',
                    'actions' => ['enregistrer_workflow', 'envoyer_notification'],
                    'metadata' => [
                        'type_notification' => 'success',
                        'destinataires' => ['redacteur', 'dgpd']
                    ]
                ],
                [
                    'value' => 'retour',
                    'label' => 'Retour',
                    'statut_suivant' => 'R_VALIDATION_NOTE_AMELIORER',
                    'message' => 'Retour pour un travail supplémentaire (Contient des « Retours » mais pas suffisamment pour qu\'il ne soit pas accepté)',
                    'actions' => ['dupliquer_document', 'copier_champs_passes', 'enregistrer_workflow', 'envoyer_notification'],
                    'metadata' => [
                        'type_notification' => 'warning',
                        'destinataires' => ['redacteur']
                    ]
                ],
                [
                    'value' => 'non_accepte',
                    'label' => 'Non accepté',
                    'statut_suivant' => 'NOTE_CONCEPTUEL',
                    'message' => 'Non accepté - Révision complète nécessaire',
                    'metadata' => [
                        'type_notification' => 'error',
                        'destinataires' => ['redacteur', 'superviseur']
                    ]
                ]
            ],

            'rules' => [
                'reference' => 'Règles internes',
                'decision_algorithm' => 'rule_based',
                'evaluation_required_fields' => ['champs_obligatoires'],

                'conditions' => [
                    [
                        'priority' => 1,
                        'name' => 'Champs obligatoires non évalués',
                        'appreciations_concernees' => [],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'champs_obligatoires_non_evalues',
                            'operator' => '>',
                            'value' => 0
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Des champs obligatoires n\'ont pas été évalués',
                        'recommandations' => ['Compléter tous les champs obligatoires']
                    ],
                    [
                        'priority' => 2,
                        'name' => 'Questions non complétées',
                        'appreciations_concernees' => [],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'non_evalues',
                            'operator' => '>',
                            'value' => 0
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Des questions n\'ont pas été complétées',
                        'recommandations' => ['Évaluer toutes les questions']
                    ],
                    [
                        'priority' => 3,
                        'name' => 'Présence de non accepté',
                        'appreciations_concernees' => ['non_accepte'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.non_accepte',
                            'operator' => '>=',
                            'value' => 1
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Une ou plusieurs réponses évaluées comme « Non accepté »',
                        'recommandations' => ['Revoir complètement les sections marquées comme « Non accepté »']
                    ],
                    [
                        'priority' => 4,
                        'name' => 'Seuil de retours dépassé',
                        'appreciations_concernees' => ['retour'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.retour',
                            'operator' => '>=',
                            'value' => 6
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Seuil de retours dépassé (6 ou plus)',
                        'recommandations' => ['Réviser en profondeur la note conceptuelle']
                    ],
                    [
                        'priority' => 5,
                        'name' => 'Tous passés',
                        'appreciations_concernees' => ['passe', 'retour'],
                        'condition' => [
                            'type' => 'and',
                            'conditions' => [
                                [
                                    'type' => 'comparison',
                                    'field' => 'count.passe',
                                    'operator' => '==',
                                    'value_field' => 'total'
                                ],
                                [
                                    'type' => 'comparison',
                                    'field' => 'count.retour',
                                    'operator' => '==',
                                    'value' => 0
                                ]
                            ]
                        ],
                        'result' => 'passe',
                        'message' => 'Toutes les questions ont été approuvées',
                        'recommandations' => []
                    ],
                    [
                        'priority' => 99,
                        'name' => 'Par défaut - Retour',
                        'appreciations_concernees' => ['passe', 'retour', 'non_accepte'],
                        'condition' => [
                            'type' => 'default'
                        ],
                        'result' => 'retour',
                        'message' => 'Retour pour un travail supplémentaire',
                        'recommandations' => ['Améliorer les points marqués comme « Retour »']
                    ]
                ]
            ],
            "accept_text" => "En remplissant et en transmettant cette note conceptuelle de projet, je confirme que les informations sont complètes et exactes à ma connaissance. Je reconnais également que le fait de fournir intentionnellement des informations inexactes ou trompeuses peut entraîner des sanctions à mon encontre."

        ]);

        $canevas->evaluation_configs = $config;

        $canevas->save();

        $this->command->info("✓ Note Conceptuelle - Configuration créée");
    }

    /**
     * Configuration pour TDR Préfaisabilité (SFD-011)
     */
    protected function seedTdrPrefaisabiliteConfig(): void
    {
        $canevas = Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-appreciation-tdrs-prefaisabilite');
        })->where('type', 'checklist')->first();

        if (!$canevas) {
            $this->command->warn('⚠️  Canevas TDR Préfaisabilité non trouvé');
            return;
        }

        // Récupérer la config existante pour ne pas écraser les structures existantes
        $existingConfig = $canevas->evaluation_configs ?? [];

        $config = array_merge($existingConfig, [
            // On garde options_notation existant s'il existe déjà
            'options_notation' => $existingConfig['options_notation'] ?? [
                [
                    'appreciation' => 'passe',
                    'libelle' => 'Passé',
                    'description' => 'Le critère est satisfaisant'
                ],
                [
                    'appreciation' => 'retour',
                    'libelle' => 'Retour',
                    'description' => 'Nécessite des améliorations'
                ],
                [
                    'appreciation' => 'non_accepte',
                    'libelle' => 'Non accepté',
                    'description' => 'Le critère n\'est pas acceptable'
                ]
            ],

            'results' => [
                [
                    'value' => 'passe',
                    'label' => 'Passé',
                    'statut_suivant' => 'SOUMISSION_RAPPORT_PF',
                    'message' => 'La présélection a été un succès (passes reçues dans toutes les questions)',
                    'actions' => ['valider_document', 'enregistrer_workflow', 'envoyer_notification'],
                    'metadata' => [
                        'type_notification' => 'success',
                        'decision_validation' => 'valider',
                        'statut_tdr' => 'valide'
                    ]
                ],
                [
                    'value' => 'retour',
                    'label' => 'Retour',
                    'statut_suivant' => 'R_TDR_PREFAISABILITE',
                    'message' => 'Retour pour un travail supplémentaire (Contient des « Retours » mais pas suffisamment pour qu\'il ne soit pas accepté)',
                    'actions' => ['dupliquer_document', 'copier_champs_passes', 'enregistrer_workflow', 'envoyer_notification'],
                    'metadata' => [
                        'type_notification' => 'warning',
                        'decision_validation' => 'reviser'
                    ]
                ],
                [
                    'value' => 'non_accepte',
                    'label' => 'Non accepté',
                    'statut_suivant' => 'TDR_PREFAISABILITE',
                    'message' => 'Non accepté - Trop de retours ou réponses non acceptées',
                    'metadata' => [
                        'type_notification' => 'error',
                        'decision_validation' => 'reviser'
                    ]
                ]
            ],

            'rules' => [
                'reference' => 'SFD-011',
                'decision_algorithm' => 'rule_based',
                'evaluation_required_fields' => ['champs_obligatoires'],

                'conditions' => [
                    [
                        'priority' => 1,
                        'name' => 'Questions non complétées',
                        'appreciations_concernees' => [],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'non_evalues',
                            'operator' => '>',
                            'value' => 0
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Non accepté - Des questions n\'ont pas été complétées',
                        'recommandations' => ['Compléter toutes les questions']
                    ],
                    [
                        'priority' => 2,
                        'name' => 'Présence de non accepté',
                        'appreciations_concernees' => ['non_accepte'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.non_accepte',
                            'operator' => '>=',
                            'value' => 1
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Non accepté - Une ou plusieurs réponses évaluées comme « Non accepté »',
                        'recommandations' => ['Revoir les sections marquées comme « Non accepté »']
                    ],
                    [
                        'priority' => 3,
                        'name' => 'Seuil de retours dépassé (SFD-011)',
                        'appreciations_concernees' => ['retour'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.retour',
                            'operator' => '>=',
                            'value' => 10
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Non accepté - Trop de retours (10 ou plus)',
                        'recommandations' => ['Réviser en profondeur le TDR']
                    ],
                    [
                        'priority' => 4,
                        'name' => 'Tous passés',
                        'appreciations_concernees' => ['passe', 'retour'],
                        'condition' => [
                            'type' => 'and',
                            'conditions' => [
                                [
                                    'type' => 'comparison',
                                    'field' => 'count.passe',
                                    'operator' => '==',
                                    'value_field' => 'total'
                                ],
                                [
                                    'type' => 'comparison',
                                    'field' => 'count.retour',
                                    'operator' => '==',
                                    'value' => 0
                                ]
                            ]
                        ],
                        'result' => 'passe',
                        'message' => 'La présélection a été un succès (passes reçues dans toutes les questions)',
                        'recommandations' => []
                    ],
                    [
                        'priority' => 99,
                        'name' => 'Par défaut - Retour',
                        'appreciations_concernees' => ['passe', 'retour', 'non_accepte'],
                        'condition' => [
                            'type' => 'default'
                        ],
                        'result' => 'retour',
                        'message' => 'Retour pour un travail supplémentaire',
                        'recommandations' => ['Améliorer les points marqués comme « Retour »']
                    ]
                ]
            ],
            "accept_text" => "En remplissant et en transmettant cette note d'appréciation des termes de référence de projet, je confirme que les informations sont complètes et exactes à ma connaissance. Je reconnais également que le fait de fournir intentionnellement des informations inexactes ou trompeuses peut entraîner des sanctions à mon encontre."
        ]);

        $canevas->evaluation_configs = $config;
        $canevas->save();

        $this->command->info("✓ TDR Préfaisabilité (SFD-011) - Configuration créée");
    }

    /**
     * Configuration pour TDR Faisabilité (SFD-015)
     */
    protected function seedTdrFaisabiliteConfig(): void
    {
        $canevas = Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-appreciation-tdrs-faisabilite');
        })->where('type', 'checklist')->first();

        if (!$canevas) {
            $this->command->warn('⚠️  Canevas TDR Faisabilité non trouvé');
            return;
        }

        // Récupérer la config existante pour ne pas écraser les structures existantes
        $existingConfig = $canevas->evaluation_configs ?? [];

        $config = array_merge($existingConfig, [
            // On garde options_notation existant s'il existe déjà
            'options_notation' => $existingConfig['options_notation'] ?? [
                [
                    'appreciation' => 'passe',
                    'libelle' => 'Passé',
                    'description' => 'Le critère est satisfaisant'
                ],
                [
                    'appreciation' => 'retour',
                    'libelle' => 'Retour',
                    'description' => 'Nécessite des améliorations'
                ],
                [
                    'appreciation' => 'non_accepte',
                    'libelle' => 'Non accepté',
                    'description' => 'Le critère n\'est pas acceptable'
                ]
            ],

            'results' => [
                [
                    'value' => 'passe',
                    'label' => 'Passé',
                    'statut_suivant' => 'SOUMISSION_RAPPORT_F',
                    'message' => 'La présélection a été un succès (passes reçues dans toutes les questions)',
                    'actions' => ['valider_document', 'enregistrer_workflow', 'envoyer_notification'],
                    'metadata' => [
                        'type_notification' => 'success',
                        'decision_validation' => 'valider',
                        'statut_tdr' => 'valide'
                    ]
                ],
                [
                    'value' => 'retour',
                    'label' => 'Retour',
                    'statut_suivant' => 'R_TDR_FAISABILITE',
                    'message' => 'Retour pour un travail supplémentaire (Contient des « Retours » mais pas suffisamment pour qu\'il ne soit pas accepté)',
                    'actions' => ['dupliquer_document', 'copier_champs_passes', 'enregistrer_workflow', 'envoyer_notification'],
                    'metadata' => [
                        'type_notification' => 'warning',
                        'decision_validation' => 'reviser'
                    ]
                ],
                [
                    'value' => 'non_accepte',
                    'label' => 'Non accepté',
                    'statut_suivant' => 'TDR_FAISABILITE',
                    'message' => 'Non accepté - Trop de retours ou réponses non acceptées',
                    'metadata' => [
                        'type_notification' => 'error',
                        'decision_validation' => 'reviser'
                    ]
                ]
            ],

            'rules' => [
                'reference' => 'SFD-015',
                'decision_algorithm' => 'rule_based',
                'evaluation_required_fields' => ['champs_obligatoires'],

                'conditions' => [
                    [
                        'priority' => 1,
                        'name' => 'Questions non complétées',
                        'appreciations_concernees' => [],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'non_evalues',
                            'operator' => '>',
                            'value' => 0
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Non accepté - Des questions n\'ont pas été complétées',
                        'recommandations' => ['Compléter toutes les questions']
                    ],
                    [
                        'priority' => 2,
                        'name' => 'Présence de non accepté',
                        'appreciations_concernees' => ['non_accepte'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.non_accepte',
                            'operator' => '>=',
                            'value' => 1
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Non accepté - Une ou plusieurs réponses évaluées comme « Non accepté »',
                        'recommandations' => ['Revoir les sections marquées comme « Non accepté »']
                    ],
                    [
                        'priority' => 3,
                        'name' => 'Seuil de retours dépassé (SFD-015)',
                        'appreciations_concernees' => ['retour'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.retour',
                            'operator' => '>=',
                            'value' => 10
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Non accepté - Trop de retours (10 ou plus)',
                        'recommandations' => ['Réviser en profondeur le TDR']
                    ],
                    [
                        'priority' => 4,
                        'name' => 'Tous passés',
                        'appreciations_concernees' => ['passe', 'retour'],
                        'condition' => [
                            'type' => 'and',
                            'conditions' => [
                                [
                                    'type' => 'comparison',
                                    'field' => 'count.passe',
                                    'operator' => '==',
                                    'value_field' => 'total'
                                ],
                                [
                                    'type' => 'comparison',
                                    'field' => 'count.retour',
                                    'operator' => '==',
                                    'value' => 0
                                ]
                            ]
                        ],
                        'result' => 'passe',
                        'message' => 'La présélection a été un succès (passes reçues dans toutes les questions)',
                        'recommandations' => []
                    ],
                    [
                        'priority' => 99,
                        'name' => 'Par défaut - Retour',
                        'appreciations_concernees' => ['passe', 'retour', 'non_accepte'],
                        'condition' => [
                            'type' => 'default'
                        ],
                        'result' => 'retour',
                        'message' => 'Retour pour un travail supplémentaire',
                        'recommandations' => ['Améliorer les points marqués comme « Retour »']
                    ]
                ]
            ],
            "accept_text" => "En remplissant et en transmettant cette note d'appréciation des termes de référence de projet, je confirme que les informations sont complètes et exactes à ma connaissance. Je reconnais également que le fait de fournir intentionnellement des informations inexactes ou trompeuses peut entraîner des sanctions à mon encontre."
        ]);

        $canevas->evaluation_configs = $config;
        $canevas->save();

        $this->command->info("✓ TDR Faisabilité (SFD-015) - Configuration créée");
    }

    /**
     * Configuration pour Contrôle Qualité
     */
    protected function seedControleQualiteConfig(): void
    {
        $canevas = Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'canevas-check-liste-suivi-controle-qualite-rapport-etude-faisabilite-preliminaire');
        })->where('type', 'checklist')->first();

        if (!$canevas) {
            $this->command->warn('⚠️  Canevas Contrôle Qualité non trouvé');
            return;
        }

        // Récupérer la config existante pour ne pas écraser les structures existantes
        $existingConfig = $canevas->evaluation_configs ?? [];

        $config = array_merge($existingConfig, [
            // On garde guide_suivi existant s'il existe déjà
            'guide_suivi' => $existingConfig['guide_suivi'] ?? [
                [
                    'option' => 'passable',
                    'libelle' => 'Passable',
                    'description' => 'Le critère de qualité est acceptable'
                ],
                [
                    'option' => 'renvoyer',
                    'libelle' => 'Renvoyer',
                    'description' => 'Nécessite une révision'
                ],
                [
                    'option' => 'non_accepte',
                    'libelle' => 'Non accepté',
                    'description' => 'Le critère qualité n\'est pas satisfait'
                ],
                [
                    'option' => 'non_applicable',
                    'libelle' => 'Non applicable',
                    'description' => 'Ce critère ne s\'applique pas au projet'
                ]
            ],

            'results' => [
                [
                    'value' => 'passe',
                    'label' => 'Passé',
                    'statut_suivant' => 'MATURITE',
                    'message' => 'Pertinence climatique passable - la présélection a été un succès',
                    'actions' => ['valider_document', 'enregistrer_workflow', 'envoyer_notification', 'mettre_a_jour_type_projet'],
                    'metadata' => [
                        'type_notification' => 'success',
                        'type_projet' => 'simple',
                        'est_mou' => true,
                        'statut_rapport' => 'valide'
                    ]
                ],
                [
                    'value' => 'renvoyer',
                    'label' => 'Renvoyer',
                    'statut_suivant' => 'R_VALIDATION_PROFIL_NOTE_AMELIORER',
                    'message' => 'Renvoyer pour révision',
                    'metadata' => [
                        'type_notification' => 'warning',
                        'statut_rapport' => 'rejete',
                        'decision' => 'rejete'
                    ]
                ],
                [
                    'value' => 'retour',
                    'label' => 'Retour',
                    'statut_suivant' => 'R_VALIDATION_PROFIL_NOTE_AMELIORER',
                    'message' => 'Retour pour amélioration',
                    'metadata' => [
                        'type_notification' => 'warning',
                        'statut_rapport' => 'rejete',
                        'decision' => 'rejete'
                    ]
                ],
                [
                    'value' => 'non_accepte',
                    'label' => 'Non accepté',
                    'statut_suivant' => 'R_VALIDATION_PROFIL_NOTE_AMELIORER',
                    'message' => 'Non accepté - Révision nécessaire',
                    'metadata' => [
                        'type_notification' => 'error',
                        'statut_rapport' => 'rejete',
                        'decision' => 'rejete'
                    ]
                ],
                [
                    'value' => 'non_applicable',
                    'label' => 'Non applicable',
                    'statut_suivant' => 'MATURITE',
                    'message' => 'Non applicable - Passage à maturité',
                    'actions' => ['valider_document', 'enregistrer_workflow', 'envoyer_notification', 'mettre_a_jour_type_projet'],
                    'metadata' => [
                        'type_notification' => 'info',
                        'type_projet' => 'simple',
                        'est_mou' => true,
                        'statut_rapport' => 'valide'
                    ]
                ]
            ],

            'rules' => [
                'reference' => 'Règles Contrôle Qualité',
                'decision_algorithm' => 'rule_based',
                'evaluation_required_fields' => [],

                'conditions' => [
                    [
                        'priority' => 1,
                        'name' => 'Questions non complétées',
                        'appreciations_concernees' => [],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'non_evalues',
                            'operator' => '>',
                            'value' => 0
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Non accepté - Des questions n\'ont pas été complétées',
                        'recommandations' => ['Compléter toutes les questions avant soumission']
                    ],
                    [
                        'priority' => 2,
                        'name' => 'Trop de non accepté',
                        'appreciations_concernees' => ['non_accepte'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.non_accepte',
                            'operator' => '>',
                            'value' => 2
                        ],
                        'result' => 'non_accepte',
                        'message' => 'Non accepté - Plus de 2 critères jugés « Non accepté »',
                        'recommandations' => ['Revoir en priorité les critères jugés « Non accepté »']
                    ],
                    [
                        'priority' => 3,
                        'name' => 'Trop de renvoyer',
                        'appreciations_concernees' => ['renvoyer'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.renvoyer',
                            'operator' => '>',
                            'value' => 4
                        ],
                        'result' => 'renvoyer',
                        'message' => 'Renvoyer - Plus de 4 critères marqués « Renvoyer »',
                        'recommandations' => ['Réviser les sections marquées comme « Renvoyer » avant nouvelle soumission']
                    ],
                    [
                        'priority' => 4,
                        'name' => 'Tous passables',
                        'appreciations_concernees' => ['passable'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.passable',
                            'operator' => '==',
                            'value_field' => 'total'
                        ],
                        'result' => 'passe',
                        'message' => 'L\'examen est réussi (toutes les notes sont « Passable »)',
                        'recommandations' => []
                    ],
                    [
                        'priority' => 5,
                        'name' => 'Tous non applicables',
                        'appreciations_concernees' => ['non_applicable'],
                        'condition' => [
                            'type' => 'comparison',
                            'field' => 'count.non_applicable',
                            'operator' => '==',
                            'value_field' => 'total'
                        ],
                        'result' => 'non_applicable',
                        'message' => 'Aucune évaluation applicable (toutes les questions sont « Non applicable »)',
                        'recommandations' => []
                    ],
                    [
                        'priority' => 99,
                        'name' => 'Par défaut - Retour',
                        'appreciations_concernees' => ['passable', 'renvoyer', 'non_accepte', 'non_applicable'],
                        'condition' => [
                            'type' => 'default'
                        ],
                        'result' => 'retour',
                        'message' => 'Retour pour amélioration (contient des notes à corriger ou à compléter)',
                        'recommandations' => ['Améliorer les critères nécessitant des corrections']
                    ]
                ]
            ],
            "accept_text" => "En remplissant et en transmettant cette note d'appréciation du rapport de faisabilite preliminaire du projet, je confirme que les informations sont complètes et exactes à ma connaissance. Je reconnais également que le fait de fournir intentionnellement des informations inexactes ou trompeuses peut entraîner des sanctions à mon encontre."
        ]);

        $canevas->evaluation_configs = $config;
        $canevas->save();

        $this->command->info("✓ Contrôle Qualité - Configuration créée");
    }
}
