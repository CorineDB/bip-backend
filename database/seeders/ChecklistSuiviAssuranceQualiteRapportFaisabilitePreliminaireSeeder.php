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
        "nom" => "Check liste de suivi du controle qualité du rapport d'étude de faisabilité préliminaire",
        "slug" => "check-liste-suivi-controle-qualite-rapport-etude-faisabilite-preliminaire",
        "description" => "Check liste de suivi du controle qualité du rapport d'étude de faisabilité préliminaire",
        "type" => "checklist",
        "evaluation_configs" => [
            "guide_suivi" => [
                [
                    "option" => "disponible",
                    "label" => "Disponible",
                    "description" => "Répond aux critères d'acceptation"
                ],
                [
                    "option" => "pas-encore-disponibles",
                    "label" => "Pas encore disponibles",
                    "description" => "Nécessite des améliorations ou éclaircissements"
                ]
            ]
        ],
        "forms" => [
            // Section 1: Identification du projet
            [
                "element_type" => "section",
                "ordre_affichage" => 1,

                "label" => "Cadre Physique du projet",
                "attribut" => "section_cadre_physique",
                "key" => "section_cadre_physique",

                "description" => "Cette section décrit les fondements physiques du projet, ses éléments géographiques, son origine et son secteur d’activités.",
                "elements" => [
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Définition du projet",
                        "attribut" => "definition_projet",
                        "type_champ" => "radio",
                        "placeholder" => "La définition du projet est-elle disponible ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Disponible",
                                        "value" => "disponible"
                                    ],
                                    [
                                        "label" => "Pas encore disponibles",
                                        "value" => "pas-encore-disponibles"
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
                        "label" => "Genèse du projet",
                        "attribut" => "genese_projet",
                        "type_champ" => "radio",
                        "placeholder" => "La genèse du projet est-elle documentée ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Disponible",
                                        "value" => "disponible"
                                    ],
                                    [
                                        "label" => "Pas encore disponibles",
                                        "value" => "pas-encore-disponibles"
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
                        "label" => "Dossier de Formulation",
                        "attribut" => "dossier_formulation",
                        "type_champ" => "radio",
                        "placeholder" => "Le dossier de formulation est-il disponible ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Disponible",
                                        "value" => "disponible"
                                    ],
                                    [
                                        "label" => "Pas encore disponibles",
                                        "value" => "pas-encore-disponibles"
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
                        "label" => "Éléments géographiques",
                        "attribut" => "elements_geographiques",
                        "type_champ" => "radio",
                        "placeholder" => "Les éléments géographiques sont-ils identifiés ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Disponible",
                                        "value" => "disponible"
                                    ],
                                    [
                                        "label" => "Pas encore disponibles",
                                        "value" => "pas-encore-disponibles"
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
                        "label" => "Éléments d’infrastructure (zones du projet)",
                        "attribut" => "elements_infrastructure",
                        "type_champ" => "radio",
                        "placeholder" => "Les éléments d’infrastructure sont-ils disponibles ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Disponible",
                                        "value" => "disponible"
                                    ],
                                    [
                                        "label" => "Pas encore disponibles",
                                        "value" => "pas-encore-disponibles"
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
                        "label" => "Secteur d’activités du projet",
                        "attribut" => "secteur_activites",
                        "type_champ" => "radio",
                        "placeholder" => "Le secteur d’activités du projet est-il défini ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Disponible",
                                        "value" => "disponible"
                                    ],
                                    [
                                        "label" => "Pas encore disponibles",
                                        "value" => "pas-encore-disponibles"
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

            // Section 2: Structures du projet
            [
                "element_type" => "section",
                "ordre_affichage" => 2,

                "label" => "Structures du projet",
                "attribut" => "section_structures_projet",
                "key" => "section_structures_projet",
                "description" => "Cette section décrit le cadre juridique, les conventions et les organismes impliqués dans la mise en œuvre du projet.",

                "elements" => [
                    [
                        "element_type" => "section",
                        "ordre_affichage" => 1,

                        "label" => "Cadre juridique du projet",
                        "attribut" => "section_cadre_juridique_projet",
                        "key" => "section_cadre_juridique_projet",
                        "description" => "Cette section décrit le cadre juridique, les conventions et les organismes impliqués dans la mise en œuvre du projet.",

                        "elements" => [
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 1,
                                "label" => "Statut juridique",
                                "attribut" => "statut_juridique",
                                "type_champ" => "radio",
                                "placeholder" => "Le statut juridique est-il établi ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Convention avec l’État",
                                "attribut" => "convention_etat",
                                "type_champ" => "radio",
                                "placeholder" => "Existe-t-il une convention avec l’État ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Convention avec les organismes publics",
                                "attribut" => "convention_organismes_publics",
                                "type_champ" => "radio",
                                "placeholder" => "Existe-t-il une convention avec des organismes publics ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                    [
                        "element_type" => "section",
                        "ordre_affichage" => 2,

                        "label" => "Organismes intéressés au projet",
                        "attribut" => "section_organisme_interesse_projet",
                        "key" => "section_organisme_interesse_projet",
                        "description" => "Cette section décrit le cadre juridique, les conventions et les organismes impliqués dans la mise en œuvre du projet.",

                        "elements" => [
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 1,
                                "label" => "Promoteur",
                                "attribut" => "promoteur",
                                "type_champ" => "radio",
                                "placeholder" => "Le promoteur du projet est-il identifié ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Opérateur",
                                "attribut" => "operateur",
                                "type_champ" => "radio",
                                "placeholder" => "L’opérateur du projet est-il identifié ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Fournisseur(s) d’assistance technique",
                                "attribut" => "fournisseurs_assistance_technique",
                                "type_champ" => "radio",
                                "placeholder" => "Les fournisseurs d’assistance technique sont-ils identifiés ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Conventions réglementant les rapports entre organismes et avec l’État",
                                "attribut" => "conventions_reglementant_rapports",
                                "type_champ" => "radio",
                                "placeholder" => "Les conventions réglementant les rapports sont-elles disponibles ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
            ],

            // Section 3: ASPECT COMMERCIAUX DU PROJET
            [
                "element_type" => "section",
                "ordre_affichage" => 3,
                "label" => "ASPECT COMMERCIAUX DU PROJET",

                "label" => "Aspects commerciaux du projet",
                "attribut" => "section_aspects_commerciaux_projet",
                "key" => "section_aspects_commerciaux_projet",
                "description" => "Cette section analyse le marché, la commercialisation et les aspects liés aux produits et services du projet.",


                "elements" => [
                    // --- Sous-section Étude du marché ---
                    [
                        "element_type" => "section",
                        "ordre_affichage" => 1,

                        "label" => "Étude du marché",
                        "attribut" => "section_etude_marche",
                        "key" => "section_etude_marche",
                        "description" => "Analyse du marché, des perspectives et de la concurrence.",

                        "elements" => [
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 1,
                                "label" => "Marché actuel",
                                "attribut" => "marche_actuel",
                                "type_champ" => "radio",
                                "placeholder" => "Les informations sur le marché actuel sont-elles disponibles ?",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Perspectives du marché (modèle de prévision)",
                                "attribut" => "perspectives_marche",
                                "type_champ" => "radio",
                                "placeholder" => "Les prévisions de marché sont-elles disponibles ?",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Concurrence directe et indirecte (produits de substitution)",
                                "attribut" => "concurrence",
                                "type_champ" => "radio",
                                "placeholder" => "Les informations sur la concurrence sont-elles disponibles ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Part du marché à prendre par le projet",
                                "attribut" => "part_marche",
                                "type_champ" => "radio",
                                "placeholder" => "Les informations sur la part du marché sont-elles disponibles ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Structure des prix actuels",
                                "attribut" => "structure_prix_actuels",
                                "type_champ" => "radio",
                                "placeholder" => "Les informations sur la structure des prix actuels sont-elles disponibles ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                    // --- Sous-section Commercialisation ---
                    [
                        "element_type" => "section",
                        "ordre_affichage" => 2,

                        "label" => "Commercialisation",
                        "attribut" => "section_commercialisation",
                        "key" => "section_commercialisation",
                        "description" => "Analyse de la commercialisation, de la distribution et de l’adéquation à la demande.",

                        "elements" => [
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 1,
                                "label" => "Structure et coût de la distribution actuelle",
                                "attribut" => "distribution_actuelle",
                                "type_champ" => "radio",
                                "placeholder" => "La structure et le coût de la distribution actuelle sont-ils disponibles ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "label" => "Commercialisation prévue pour le projet",
                                "attribut" => "commercialisation_prevue",
                                "type_champ" => "radio",
                                "placeholder" => "La stratégie de commercialisation prévue est-elle définie ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "label" => "Organisation",
                                "attribut" => "organisation",
                                "type_champ" => "radio",
                                "placeholder" => "L’organisation de la commercialisation est-elle disponible ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "label" => "Modalités",
                                "attribut" => "modalites",
                                "type_champ" => "radio",
                                "placeholder" => "Les modalités de commercialisation sont-elles définies ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "label" => "Garantie",
                                "attribut" => "garantie",
                                "type_champ" => "radio",
                                "placeholder" => "Les garanties commerciales sont-elles définies ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "label" => "Coûts",
                                "attribut" => "couts",
                                "type_champ" => "radio",
                                "placeholder" => "Les coûts commerciaux sont-ils définis ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "label" => "Coûts",
                                "attribut" => "couts",
                                "type_champ" => "radio",
                                "placeholder" => "Les coûts commerciaux sont-ils définis ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "label" => "Adéquation des produits à la demande",
                                "attribut" => "adequation_produits",
                                "type_champ" => "radio",
                                "placeholder" => "Les produits sont-ils en adéquation avec la demande ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "ordre_affichage" => 8,
                                "label" => "Étude des transports",
                                "attribut" => "etude_transports",
                                "type_champ" => "radio",
                                "placeholder" => "L’étude des transports a-t-elle été réalisée ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "ordre_affichage" => 9,
                                "label" => "Produits secondaires éventuels",
                                "attribut" => "produits_secondaires",
                                "type_champ" => "radio",
                                "placeholder" => "Les produits secondaires éventuels ont-ils été identifiés ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
                                "ordre_affichage" => 10,
                                "label" => "Approvisionnement",
                                "attribut" => "approvisionnement",
                                "type_champ" => "radio",
                                "placeholder" => "Les conditions d’approvisionnement sont-elles définies ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            ["label" => "Disponible", "value" => "disponible"],
                                            ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
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
                                        "in" => ["disponible", "pas-encore-disponibles"],
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
            ],

            // Section 4: Planification et gouvernance du projet
            [
                "element_type" => "section",
                "ordre_affichage" => 4,
                "label" => "Description technique du projet",
                "attribut" => "section_description_technique_projet",
                "key" => "section_description_technique_projet",
                "description" => "Description technique du projet",
                "elements" => [
                    // --- Choix techniques et justification ---
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 1,
                        "label" => "Choix techniques et justification",
                        "attribut" => "choix_techniques_justification",
                        "type_champ" => "radio",
                        "placeholder" => "Les choix techniques sont-ils disponibles ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    ["label" => "Disponible", "value" => "disponible"],
                                    ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
                                ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Fournissez des détails ou justifications (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => ["disable" => false, "visible" => true, "conditions" => []],
                            "validations_rules" => [
                                "in" => ["disponible", "pas-encore-disponibles"],
                                "string" => true,
                                "required" => true,
                                "explanation_validation" => ["min" => 50, "string" => true, "required" => false]
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],

                    // --- Échelonnement des réalisations ---
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 2,
                        "label" => "Échelonnement des réalisations",
                        "attribut" => "echelonnement_realisations",
                        "type_champ" => "radio",
                        "placeholder" => "Les informations sur l’échelonnement des réalisations sont-elles disponibles ?",
                        "is_required" => true,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    ["label" => "Disponible", "value" => "disponible"],
                                    ["label" => "Pas encore disponibles", "value" => "pas-encore-disponibles"]
                                ],
                                "show_explanation" => true,
                                "explanation_placeholder" => "Explications détaillées (optionnel)",
                                "explanation_min_length" => 50
                            ],
                            "conditions" => ["disable" => false, "visible" => true, "conditions" => []],
                            "validations_rules" => ["in" => ["disponible", "pas-encore-disponibles"], "string" => true, "required" => true, "explanation_validation" => ["min" => 50, "string" => true, "required" => false]]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => false
                    ],
                    // --- Investissements spécifiques du projet ---
                    [
                        "element_type" => "section",
                        "ordre_affichage" => 3,
                        "label" => "Investissements spécifiques du projet",
                        "attribut" => "section_investissements_specifiques",
                        "key" => "section_investissements_specifiques",
                        "description" => "Investissements spécifiques du projet",

                        "elements" => [

                            // --- Description technique des investissements ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 4,
                                "label" => "Description technique des investissements",
                                "attribut" => "description_technique_investissements",
                                "type_champ" => "radio",
                                "placeholder" => "Disponible ou pas ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                            // --- Justification des choix type de matériel ou bâtiment ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 5,
                                "label" => "Justification des choix type de matériel ou bâtiment",
                                "attribut" => "justification_choix_materiel_batiment",
                                "type_champ" => "radio",
                                "placeholder" => "Disponible ou pas ?",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                            // --- Modalités de réalisation de l’investissement et passation des marchés ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 6,
                                "label" => "Modalités de réalisation de l’investissement et passation des marchés",
                                "attribut" => "modalites_realisation_investissement",
                                "type_champ" => "radio",
                                "placeholder" => "Disponible ou pas ?",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                        ],
                    ],
                    // --- Sous-section Autres investissements ---
                    [
                        "element_type" => "section",
                        "ordre_affichage" => 4,

                        "label" => "Autres investissements",
                        "attribut" => "section_autres_investissements",
                        "key" => "section_autres_investissements",
                        "description" => "Autres investissements",

                        "elements" => [
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 1,
                                "label" => "Liés au projet et financés dans le cadre",
                                "attribut" => "projet_finance_cadre",
                                "type_champ" => "radio",
                                "placeholder" => "Liés au projet et financés dans le cadre",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Liés au projet mais non financés dans le cadre",
                                "attribut" => "projet_non_finance_cadre",
                                "type_champ" => "radio",
                                "placeholder" => "Liés au projet mais non financés dans le cadre",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                    // --- Sous-section Personnel et formation ---
                    [
                        "element_type" => "section",
                        "ordre_affichage" => 5,
                        "label" => "Personnel et formation",
                        "attribut" => "section_personnel_formation",
                        "key" => "autres_investissements",
                        "description" => "Personnel et formation",

                        "elements" => [
                            // --- Direction et cadre ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 1,
                                "label" => "Direction et cadre",
                                "attribut" => "direction_cadre",
                                "type_champ" => "radio",
                                "placeholder" => "Direction et cadre",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                            // --- Personnel qualifié ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 2,
                                "label" => "Personnel qualifié",
                                "attribut" => "personnel_qualifie",
                                "type_champ" => "radio",
                                "placeholder" => "Personnel qualifié",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                            // --- Main d’oeuvre non qualifié ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 3,
                                "label" => "Main d’oeuvre non qualifié",
                                "attribut" => "main_oeuvre_non_qualifie",
                                "type_champ" => "radio",
                                "placeholder" => "Main d’oeuvre non qualifié",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                            // --- Relève des expatriés par les nationaux ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 4,
                                "label" => "Relève des expatriés par les nationaux",
                                "attribut" => "releve_expatries_nationaux",
                                "type_champ" => "radio",
                                "placeholder" => "Relève des expatriés par les nationaux",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                            // --- Organigramme ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 5,
                                "label" => "Organigramme",
                                "attribut" => "organigramme",
                                "type_champ" => "radio",
                                "placeholder" => "Organigramme",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                            // --- Ressources en main d’oeuvre ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 6,
                                "label" => "Ressources en main d’oeuvre",
                                "attribut" => "ressources_main_oeuvre",
                                "type_champ" => "radio",
                                "placeholder" => "Ressources en main d’oeuvre",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                            // --- Fiches de postes ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 7,
                                "label" => "Fiches de postes",
                                "attribut" => "fiches_postes",
                                "type_champ" => "radio",
                                "placeholder" => "Fiches de postes",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                            // --- Programme de formation ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 8,
                                "label" => "Programme de formation",
                                "attribut" => "programme_formation",
                                "type_champ" => "radio",
                                "placeholder" => "Programme de formation",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                            // --- Plan de relève des expatriés par des nationaux ---
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 9,
                                "label" => "Plan de relève des expatriés par des nationaux",
                                "attribut" => "plan_releve_expatries_nationaux",
                                "type_champ" => "radio",
                                "placeholder" => "Plan de relève des expatriés par des nationaux",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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

                    // --- Objectifs de production ---
                    [
                        "element_type" => "field",
                        "ordre_affichage" => 6,
                        "label" => "Objectifs de production",
                        "attribut" => "objectifs_production",
                        "type_champ" => "radio",
                        "placeholder" => "Disponible ou pas ?",

                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => true,
                        "meta_options" => [
                            "configs" => [
                                "options" => [
                                    [
                                        "label" => "Disponible",
                                        "value" => "disponible"
                                    ],
                                    [
                                        "label" => "Pas encore disponibles",
                                        "value" => "pas-encore-disponibles"
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

                    // --- Sous-section Exploitation ---
                    [
                        "element_type" => "section",
                        "ordre_affichage" => 7,

                        "label" => "Exploitation",
                        "attribut" => "section_exploitation",
                        "key" => "section_exploitation",
                        "description" => "Exploitation",

                        "elements" => [
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 1,
                                "label" => "Description technique de l’exploitation",
                                "attribut" => "description_technique_exploitation",
                                "type_champ" => "radio",
                                "placeholder" => "Description technique de l’exploitation",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Justification des coefficients techniques retenus",
                                "attribut" => "justification_coefficients_techniques_retenus",
                                "type_champ" => "radio",
                                "placeholder" => "Justification des coefficients techniques retenus",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Évaluation technique du fonds de roulement et de son évaluation dans le temps",
                                "attribut" => "evaluation_technique_fonds_roulement_evaluation_temps",
                                "type_champ" => "radio",
                                "placeholder" => "Évaluation technique du fonds de roulement et de son évaluation dans le temps",

                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
            ],

            // Section 5: COÛT DU PROJET
            [
                "element_type" => "section",
                "ordre_affichage" => 5,
                "label" => "COÛT DU PROJET",
                "attribut" => "section_cout_projet",
                "description" => "COÛT DU PROJET",
                "elements" => [
                    [

                        // --- Sous-section Investissements ---
                        "element_type" => "section",
                        "ordre_affichage" => 7,

                        "label" => "Investissements",
                        "attribut" => "section_investissements",
                        "key" => "section_investissements",
                        "description" => "Investissements",

                        "elements" => [
                            [
                                "element_type" => "field",
                                "ordre_affichage" => 1,
                                "label" => "Evaluation des coûts du projet",
                                "attribut" => "evaluation_couts_projet",
                                "type_champ" => "radio",
                                "placeholder" => "Evaluation des coûts du projet",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Étalement des investissements dans le temps : calendrier des dépenses",
                                "attribut" => "etalement_investissements",
                                "type_champ" => "radio",
                                "placeholder" => "Étalement des investissements dans le temps : calendrier des dépenses",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Incidence des incertitudes techniques sur les délais et les coûts",
                                "attribut" => "incidence_incertitudes_techniques",
                                "type_champ" => "radio",
                                "placeholder" => "Incidence des incertitudes techniques sur les délais et les coûts",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                                "label" => "Évaluation des amortissements techniques",
                                "attribut" => "evaluation_amortissements_techniques",
                                "type_champ" => "radio",
                                "placeholder" => "Évaluation des amortissements techniques",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => true,
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [
                                            [
                                                "label" => "Disponible",
                                                "value" => "disponible"
                                            ],
                                            [
                                                "label" => "Pas encore disponibles",
                                                "value" => "pas-encore-disponibles"
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
                'slug' => 'canevas-check-liste-suivi-controle-qualite-rapport-etude-faisabilite-preliminaire'
            ], [
                'nom' => "Canevas du check liste de suivi pour l'assurance qualité du rapport d'étude de faisabilité",
                'slug' => 'canevas-check-liste-suivi-controle-qualite-rapport-etude-faisabilite-preliminaire',
                'format' => 'checklist'
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
