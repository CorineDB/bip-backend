<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\Champ;

class CanevasRedactionEtudeFaisabiliteTechniqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $document = Document::firstOrCreate([
            'categorie' => 'canevas-etude-faisabilite-technique'
        ], [
            'nom' => 'Canevas d\'étude de faisabilité technique',
            'description' => 'Canevas standardisé pour la rédaction d\'études de faisabilité technique des projets',
            'type' => 'formulaire',
            'categorie' => 'canevas-etude-faisabilite-technique'
        ]);

        $champs = [
            [
                "element_type" => "field",
                "ordre_affichage" => 1,
                "label" => "RÉSUMÉ",
                "attribut" => "resume",
                "type_champ" => "textarea",
                "placeholder" => "Brève description de l'étude de faisabilité technique",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 4,
                        "max_length" => 2000,
                        "min_length" => 100
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 2000,
                        "min" => 100,
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
                "label" => "Collecte et compilation d'informations préalables",
                "attribut" => "informations_prealables",
                "type_champ" => "textarea",
                "placeholder" => "Ne pas oublier celles contenues dans le rapport de formulation du projet",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 6,
                        "max_length" => 4000,
                        "min_length" => 200
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 4000,
                        "min" => 200,
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
                "label" => "Fixation/détermination de la capacité de production",
                "attribut" => "capacite_production",
                "type_champ" => "textarea",
                "placeholder" => "Déterminez la capacité de production optimale en fonction des objectifs du projet",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 5,
                        "max_length" => 3000,
                        "min_length" => 150
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 3000,
                        "min" => 150,
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
                "label" => "Description des caractéristiques des matériaux et des intrants",
                "attribut" => "materiaux_intrants",
                "type_champ" => "textarea",
                "placeholder" => "Décrivez les matériaux nécessaires, leurs spécifications techniques et sources d'approvisionnement",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 6,
                        "max_length" => 4000,
                        "min_length" => 200
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 4000,
                        "min" => 200,
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
                "label" => "Technologie et/ou processus de fabrication",
                "attribut" => "technologie_processus",
                "type_champ" => "textarea",
                "placeholder" => "Choix d'une technologie ou du processus de fabrication ou de production des biens et services",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 6,
                        "max_length" => 4000,
                        "min_length" => 200
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 4000,
                        "min" => 200,
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
                "label" => "Structure organisationnelle",
                "attribut" => "structure_organisationnelle",
                "type_champ" => "textarea",
                "placeholder" => "Décrire la structure organisationnelle actuelle de projet de développement similaire",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 5,
                        "max_length" => 3000,
                        "min_length" => 150
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 3000,
                        "min" => 150,
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
                "label" => "Description de la machinerie et de l'équipement",
                "attribut" => "machinerie_equipement",
                "type_champ" => "textarea",
                "placeholder" => "Détaillez les machines et équipements nécessaires, leurs spécifications et coûts",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 6,
                        "max_length" => 4000,
                        "min_length" => 200
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 4000,
                        "min" => 200,
                        "string" => true,
                        "required" => true
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => false
            ],
            [
                "element_type" => "field",
                "ordre_affichage" => 8,
                "label" => "Définition des aspects organisationnels et de la phase de mise en œuvre du projet",
                "attribut" => "aspects_organisationnels_mise_en_oeuvre",
                "type_champ" => "textarea",
                "placeholder" => "Décrivez l'organisation, les responsabilités et le phasage de la mise en œuvre",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 6,
                        "max_length" => 4000,
                        "min_length" => 200
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 4000,
                        "min" => 200,
                        "string" => true,
                        "required" => true
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => false
            ],
            [
                "element_type" => "field",
                "ordre_affichage" => 9,
                "label" => "Description des bâtiments et infrastructures",
                "attribut" => "batiments_infrastructures",
                "type_champ" => "textarea",
                "placeholder" => "Décrivez les besoins en bâtiments, infrastructures et aménagements nécessaires",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 5,
                        "max_length" => 3500,
                        "min_length" => 150
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 3500,
                        "min" => 150,
                        "string" => true,
                        "required" => true
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => false
            ],
            [
                "element_type" => "field",
                "ordre_affichage" => 10,
                "label" => "Choix d'un site et localisation",
                "attribut" => "choix_site_localisation",
                "type_champ" => "textarea",
                "placeholder" => "Justifiez le choix du site en tenant compte des critères techniques, économiques et environnementaux",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 5,
                        "max_length" => 3000,
                        "min_length" => 150
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 3000,
                        "min" => 150,
                        "string" => true,
                        "required" => true
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => false
            ],
            [
                "element_type" => "field",
                "ordre_affichage" => 11,
                "label" => "Estimation des coûts d'investissement et des coûts de mise en exploitation du projet",
                "attribut" => "estimation_couts",
                "type_champ" => "textarea",
                "placeholder" => "Détaillez les coûts d'investissement initiaux et les coûts récurrents d'exploitation",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 6,
                        "max_length" => 4000,
                        "min_length" => 200
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 4000,
                        "min" => 200,
                        "string" => true,
                        "required" => true
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => false
            ],
            [
                "element_type" => "field",
                "ordre_affichage" => 12,
                "label" => "Élaboration de l'échéancier ou du planning préliminaire d'implantation du projet",
                "attribut" => "planning_implantation",
                "type_champ" => "textarea",
                "placeholder" => "Présentez le chronogramme détaillé des activités avec les jalons principaux",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 5,
                        "max_length" => 3500,
                        "min_length" => 150
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 3500,
                        "min" => 150,
                        "string" => true,
                        "required" => true
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => false
            ],
            [
                "element_type" => "field",
                "ordre_affichage" => 13,
                "label" => "Formulation des conclusions et recommandations",
                "attribut" => "conclusions_recommandations",
                "type_champ" => "textarea",
                "placeholder" => "Synthétisez les principales conclusions et formulez des recommandations pour la suite du projet",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => true,
                "meta_options" => [
                    "configs" => [
                        "rows" => 5,
                        "max_length" => 3000,
                        "min_length" => 150
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "max" => 3000,
                        "min" => 150,
                        "string" => true,
                        "required" => true
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => false
            ]
        ];

        foreach ($champs as $champData) {
            Champ::firstOrCreate([
                'attribut' => $champData['attribut'],
                'documentId' => $document->id
            ], [
                'label' => $champData['label'],
                'attribut' => $champData['attribut'],
                'type_champ' => $champData['type_champ'],
                'documentId' => $document->id,
                'sectionId' => null,
                'is_required' => $champData['is_required'],
                'ordre_affichage' => $champData['ordre_affichage'],
                'placeholder' => $champData['placeholder'],
                'default_value' => $champData['default_value'],
                'meta_options' => $champData['meta_options'],
                'isEvaluated' => $champData['isEvaluated'],
                'champ_standard' => $champData['champ_standard'],
                'startWithNewLine' => $champData['startWithNewLine']
            ]);
        }
    }
}