<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanevasChecklistRapportPrefaisabiliteSeeder extends Seeder
{
    protected $documentData = [
        "nom" => "Checklist de suivi rapport de préfaisabilité",
        "slug" => "checklist-suivi-rapport-prefaisabilite",
        "description" => "Checklist de suivi rapport de préfaisabilité",
        "type" => "formulaire",
        "categorieId" => 4,
        "forms" => [
            // Section 1: Identification du projet
            [
                "element_type" => "section",
                "ordre_affichage" => 1,
                "label" => "Identification du projet",
                "attribut" => "section_identification_projet",
                "description" => "Éléments fondamentaux d'identification et de définition du projet",
                "is_required" => true,
                "meta_options" => [
                    "configs" => [
                        "collapsible" => true,
                        "collapsed" => false
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => true,
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Problématique à résoudre",
                        "attribut" => "problematique_a_resoudre",
                        "type_champ" => "textarea",
                        "placeholder" => "Décrivez la problématique que le projet vise à résoudre",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Définissez les objectifs et résultats attendus du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez les besoins identifiés pour ce projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Identifiez la population cible et les bénéficiaires du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Décrivez les alternatives envisagées et justifiez le choix retenu",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                "description" => "Évaluation de la viabilité économique et financière du projet",
                "is_required" => true,
                "meta_options" => [
                    "configs" => [
                        "collapsible" => true,
                        "collapsed" => false
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => true,
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Viabilité économique",
                        "attribut" => "viabilite_economique",
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez la viabilité économique du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Détaillez le plan de financement du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez la rentabilité attendue du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Détaillez les délais de mise en œuvre du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Décrivez les mesures de contrôle des adaptations pour les projets à haut risque",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez les vulnérabilités du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez les risques financiers associés au projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                "description" => "Évaluation des impacts sociaux et environnementaux du projet",
                "is_required" => true,
                "meta_options" => [
                    "configs" => [
                        "collapsible" => true,
                        "collapsed" => false
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => true,
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Impact sur les populations locales",
                        "attribut" => "impact_populations_locales",
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez l'impact sur les populations locales (création d'emplois, réduction de la pauvreté, etc.)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez l'impact sur l'environnement (durabilité, effets sur les ressources naturelles)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Décrivez les mécanismes d'atténuation des impacts négatifs",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez la prise en compte du genre et de l'inclusion sociale",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                "is_required" => true,
                "meta_options" => [
                    "configs" => [
                        "collapsible" => true,
                        "collapsed" => false
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => true,
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Calendrier prévisionnel (chronogramme de projet)",
                        "attribut" => "calendrier_previsionnel",
                        "type_champ" => "textarea",
                        "placeholder" => "Détaillez le calendrier prévisionnel et le chronogramme du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Décrivez la structure de gouvernance (instances de décision, comité de suivi)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Détaillez le système de suivi et évaluation (indicateurs de performance)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Identifiez les risques potentiels (techniques, économiques, environnementaux, sociaux)",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Décrivez le plan de gestion et d'atténuation des risques",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                "is_required" => true,
                "meta_options" => [
                    "configs" => [
                        "collapsible" => true,
                        "collapsed" => false
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => true,
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Cadre législatif et réglementaire",
                        "attribut" => "cadre_legislatif_reglementaire",
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez le cadre législatif et réglementaire applicable",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
                        "type_champ" => "textarea",
                        "placeholder" => "Analysez la conformité avec les accords internationaux",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "rows" => 4,
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "max" => 3000,
                                "min" => 50,
                                "string" => true,
                                "required" => true
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
            $categorieDocument = CategorieDocument::firstOrCreate([
                'slug' => "check-list-suivi-rapport-prefaisabilite",
            ], [
                'nom' => "Check-list de suivi rapport de préfaisabilité",
                'slug' => "check-list-suivi-rapport-prefaisabilite",
                "description" => "Canevas standardisés pour les check-list de suivi de rédaction de rapports",
                "format" => "formulaire"
            ]);

            // Extraire les données relationnelles avant création
            $formsData = $this->documentData['forms'] ?? [];

            // Nettoyer les données du document principal
            $documentData = collect($this->documentData)->except(['forms', 'champs', 'id'])->toArray();

            $documentData = array_merge($documentData, [
                "categorieId" => $categorieDocument->id
            ]);

            // Créer le document principal
            $document = Document::updateOrCreate(['slug' => "checklist-suivi-rapport-prefaisabilite"], $documentData);

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
            'label' => $sectionData['label'],
            'attribut' => $sectionData['attribut'] ?? null,
            'description' => $sectionData['description'] ?? null,
            'is_required' => $sectionData['is_required'] ?? false,
            'ordre_affichage' => $sectionData['ordre_affichage'],
            'element_type' => 'section',
            'meta_options' => $sectionData['meta_options'] ?? [],
            'champ_standard' => $sectionData['champ_standard'] ?? false,
            'startWithNewLine' => $sectionData['startWithNewLine'] ?? true,
            'documentId' => $document->id,
            'parent_section_id' => $parentSection ? $parentSection->id : null
        ];

        // Créer la section
        $section = $document->champs()->updateOrCreate([
            'attribut' => $sectionData['attribut']
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
            'element_type' => 'field',
            'type_champ' => $champData['type_champ'],
            'meta_options' => $champData['meta_options'] ?? [],
            'startWithNewLine' => $champData['startWithNewLine'] ?? false,
            'documentId' => $document->id,
            'parent_section_id' => $parentSection ? $parentSection->id : null
        ];

        // Créer le champ
        $document->champs()->updateOrCreate([
            'attribut' => $champData['attribut']
        ], $champAttributes);
    }
}