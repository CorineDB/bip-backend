<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanevasChecklistRapportPrefaisabiliteSeeder extends Seeder
{
    protected $documentData = [
        "nom" => "Check liste de suivi rapport prefaisabilite",
        "slug" => "canevas-check-liste-suivi-rapport-prefaisabilite",
        "description" => "Check liste de suivi rapport prefaisabilite",
        "type" => "checklist",
        "evaluation_configs" => [
            "guide_notation" => [
                [
                    "appreciation" => "passe",
                    "label" => "Passé",
                    "description" => "Répond aux critères d'acceptation",
                    "couleur" => "#22c55e"
                ],
                [
                    "appreciation" => "retour",
                    "label" => "Retour",
                    "description" => "Nécessite des améliorations ou éclaircissements",
                    "couleur" => "#f59e0b"
                ],
                [
                    "appreciation" => "non_accepte",
                    "label" => "Non accepté",
                    "description" => "Ne répond pas aux critères minimums",
                    "couleur" => "#ef4444"
                ]
            ],
            "criteres_evaluation" => [
                "commentaire_obligatoire" => true,
                "seuil_acceptation" => 0,
                "regles_decision" => [
                    "succes" => "La présélection a été un succès (passes reçues dans toutes les questions)",
                    "retour" => "Retour pour un travail supplémentaire (Non, « Non accepté » contient des « Retours » mais pas suffisamment pour qu'il ne soit pas accepté)",
                    "non_accepte" => "Non accepté - Si des questions n'ont pas été complétées OU Si une réponse à une question a été évaluée comme « Non acceptée » OU Si 10 ou plus des réponses ont été évaluées comme « Retour »"
                ]
            ],
            "workflow" => [
                "etapes" => [
                    "soumission" => "Soumission du TDR pour appréciation",
                    "appreciation" => "Évaluation par l'évaluateur",
                    "decision" => "Décision finale basée sur les règles"
                ]
            ]
        ],
        "forms" => [
            // Section 1: Identification du projet
            [
                "element_type" => "section",
                "ordre_affichage" => 1,
                "label" => "Identification du projet",
                "attribut" => "section_identification_projet",
                "key" => "section_identification_projet",
                "description" => "Cette section évalue le projet en prenant en compte les besoins des bénéficiaires et les alternatives possibles. Cela permet de justifier pourquoi le projet est nécessaire et quel impact il aura sur la population cible",
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Problématique à résoudre",
                        "attribut" => "problematique_a_resoudre",
                        "type_champ" => "radio",
                        "placeholder" => "La problématique à résoudre est-elle clairement définie ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 2,
                        "label" => "Objectifs et résultats attendus de l'évaluation",
                        "attribut" => "objectifs_resultats_attendus",
                        "type_champ" => "radio",
                        "placeholder" => "Les objectifs et résultats attendus sont-ils définis ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 3,
                        "label" => "Analyse des besoins",
                        "attribut" => "analyse_des_besoins",
                        "type_champ" => "radio",
                        "placeholder" => "L'analyse des besoins est-elle réalisée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 4,
                        "label" => "Population cible/ bénéficiaires",
                        "attribut" => "population_cible_beneficiaires",
                        "type_champ" => "radio",
                        "placeholder" => "La population cible et les bénéficiaires sont-ils identifiés ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 5,
                        "label" => "Alternatives considérées",
                        "attribut" => "alternatives_considerees",
                        "type_champ" => "radio",
                        "placeholder" => "Les alternatives sont-elles considérées et le choix est-il justifié ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ]
                ]
            ],

            // Section 2: Analyse économique et financière
            [
                "element_type" => "section",
                "ordre_affichage" => 2,
                "label" => "Analyse économique et financière",
                "attribut" => "section_analyse_economique_financiere",
                "key" => "section_analyse_economique_financiere",
                "description" => "Cette section évalue la faisabilité financière du projet en tenant compte des coûts, des modes de financement, et de la rentabilité attendue. Une analyse approfondie des risques financiers et des options de financement doit également être réalisée pour garantir la viabilité du projet.",
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Viabilité économique",
                        "attribut" => "viabilite_economique",
                        "type_champ" => "radio",
                        "placeholder" => "La viabilité économique est-elle analysée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 2,
                        "label" => "Plan de financement",
                        "attribut" => "plan_de_financement",
                        "type_champ" => "radio",
                        "placeholder" => "Le plan de financement est-il détaillé ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 3,
                        "label" => "Analyse de la rentabilité",
                        "attribut" => "analyse_rentabilite",
                        "type_champ" => "radio",
                        "placeholder" => "L'analyse de rentabilité est-elle réalisée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 4,
                        "label" => "Délais de mise en œuvre",
                        "attribut" => "delais_mise_en_oeuvre",
                        "type_champ" => "radio",
                        "placeholder" => "Les délais de mise en œuvre sont-ils détaillés ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 5,
                        "label" => "Contrôle des adaptations pour les projets à haut risque",
                        "attribut" => "controle_adaptations_haut_risque",
                        "type_champ" => "radio",
                        "placeholder" => "Les mesures de contrôle pour les projets à haut risque sont-elles décrites ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 6,
                        "label" => "Analyse des vulnérabilités",
                        "attribut" => "analyse_vulnerabilites",
                        "type_champ" => "radio",
                        "placeholder" => "L'analyse des vulnérabilités est-elle réalisée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 7,
                        "label" => "Analyse des risques financiers",
                        "attribut" => "analyse_risques_financiers",
                        "type_champ" => "radio",
                        "placeholder" => "L'analyse des risques financiers est-elle réalisée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ]
                ]
            ],

            // Section 3: Impact social et environnemental
            [
                "element_type" => "section",
                "ordre_affichage" => 3,
                "label" => "Impact social et environnemental",
                "attribut" => "section_impact_social_environnemental",
                "description" => "On analyse l’impact du projet sur la société et l’environnement. Cela inclut les effets positifs (emplois, bien-être social) et négatifs (pollution, exploitation des ressources). Il est important de prévoir des mesures d’atténuation pour limiter les impacts négatifs.",
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Impact sur les populations locales",
                        "attribut" => "impact_populations_locales",
                        "type_champ" => "radio",
                        "placeholder" => "L'impact sur les populations locales est-il analysé ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 2,
                        "label" => "Impact sur l'environnement",
                        "attribut" => "impact_environnement",
                        "type_champ" => "radio",
                        "placeholder" => "L'impact sur l'environnement est-il analysé ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 3,
                        "label" => "Mécanismes d'atténuation des impacts négatifs",
                        "attribut" => "mecanismes_attenuation_impacts",
                        "type_champ" => "radio",
                        "placeholder" => "Les mécanismes d'atténuation des impacts négatifs sont-ils décrits ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 4,
                        "label" => "Prise en compte du genre et de l'inclusion sociale",
                        "attribut" => "genre_inclusion_sociale",
                        "type_champ" => "radio",
                        "placeholder" => "La prise en compte du genre et de l'inclusion sociale est-elle analysée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ]
                ]
            ],

            // Section 4: Planification et gouvernance du projet
            [
                "element_type" => "section",
                "ordre_affichage" => 4,
                "label" => "Planification et gouvernance du projet",
                "attribut" => "section_planification_gouvernance",
                "description" => "Organisation, planification et gestion du projet",
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Calendrier prévisionnel (chronogramme de projet)",
                        "attribut" => "calendrier_previsionnel",
                        "type_champ" => "radio",
                        "placeholder" => "Le calendrier prévisionnel et le chronogramme sont-ils détaillés ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 2,
                        "label" => "Structure de gouvernance",
                        "attribut" => "structure_gouvernance",
                        "type_champ" => "radio",
                        "placeholder" => "La structure de gouvernance est-elle décrite ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 3,
                        "label" => "Suivi et évaluation",
                        "attribut" => "suivi_evaluation",
                        "type_champ" => "radio",
                        "placeholder" => "Le système de suivi et évaluation est-il détaillé ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 4,
                        "label" => "Identification des risques potentiels",
                        "attribut" => "identification_risques_potentiels",
                        "type_champ" => "radio",
                        "placeholder" => "Les risques potentiels sont-ils identifiés ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 5,
                        "label" => "Plan de gestion et d'atténuation des risques",
                        "attribut" => "plan_gestion_attenuation_risques",
                        "type_champ" => "radio",
                        "placeholder" => "Le plan de gestion et d'atténuation des risques est-il décrit ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ]
                ]
            ],

            // Section 5: Conformité légale et réglementaire
            [
                "element_type" => "section",
                "ordre_affichage" => 5,
                "label" => "Conformité légale et réglementaire",
                "attribut" => "section_conformite_legale_reglementaire",
                "description" => "Respect du cadre légal et réglementaire applicable",
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Cadre législatif et réglementaire",
                        "attribut" => "cadre_legislatif_reglementaire",
                        "type_champ" => "radio",
                        "placeholder" => "Le cadre législatif et réglementaire est-il analysé ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 2,
                        "label" => "Conformité avec les accords internationaux",
                        "attribut" => "conformite_accords_internationaux",
                        "type_champ" => "radio",
                        "placeholder" => "La conformité avec les accords internationaux est-elle analysée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" =>[
                                  [
                                    "label"=> "Disponible",
                                    "value"=> "disponible"
                                  ],
                                  [
                                    "label"=> "Pas encore disponibles",
                                    "value"=> "pas-encore-disponibles"
                                  ]
                            ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "in" => [
                                    "disponible",
                                    "pas-encore-disponibles"
                                ],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => [
                                    "min" => 50,
                                    "string" => true,
                                    "required" => false
                                ]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ]
                ]
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
            // Créer la catégorie de document pour les checklists
            $categorieDocument = CategorieDocument::updateOrCreate([
                'slug' => "canevas-check-liste-suivi-rapport-prefaisabilite",
            ], [
                'nom' => "Canevas check liste de suivi rapport de préfaisabilité",
                "description" => "Canevas standardisés pour les check-list de suivi de rédaction de rapports",
                "format" => "checklist"
            ]);

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

            // Traiter les sections et leurs éléments
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
            'intitule' => $sectionData['label'],
            'slug' => $sectionData['attribut'] ?? null,
            'description' => $sectionData['description'] ?? null,
            'documentId' => $document->id,
            'parentSectionId' => $parentSection ? $parentSection->id : null,
            'ordre_affichage' => $sectionData['ordre_affichage'],
        ];

        // Créer la section en utilisant intitule et documentId pour l'unicité
        $section = $document->sections()->updateOrCreate([
            'intitule' => $sectionData['label'],
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

        // Créer le champ en utilisant la contrainte d'unicité complète
        \App\Models\Champ::updateOrCreate([
            'attribut' => $champData['attribut'],
            'sectionId' => $parentSection ? $parentSection->id : null,
            'documentId' => $document->id
        ], $champAttributes);
    }
}
