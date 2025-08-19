<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\CategorieDocument;
use App\Models\Champ;
use App\Models\ChampSection;

class CanevasAppreciationTDRSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer ou récupérer la catégorie
        $categorie = CategorieDocument::firstOrCreate([
            'slug' => 'canevas-appreciation-tdr'
        ], [
            'nom' => 'Canevas d\'appréciation des tdrs',
            'description' => 'Catégorie pour les canevas d\'appréciation des termes de référence'
        ]);

        // Créer le document canevas
        $document = Document::updateOrCreate([
            'slug' => 'canevas-appreciation-tdr'
        ], [
            'nom' => 'Canevas d\'appréciation des termes de reference',
            'description' => 'Canevas d\'appréciation des termes de reference',
            'type' => 'formulaire',
            'categorieId' => $categorie->id,
            'evaluation_configs' => [
                'options_notation' => [
                    [
                        'libelle' => 'Passé',
                        'appreciation' => 'passe',
                        'description' => 'L\'élément est accepté et passe à l\'étape suivante'
                    ],
                    [
                        'libelle' => 'Retour',
                        'appreciation' => 'retour',
                        'description' => 'L\'élément nécessite une amélioration avant validation.'
                    ],
                    [
                        'libelle' => 'Non accepté',
                        'appreciation' => 'non_accepte',
                        'description' => 'L\'élément ne correspond pas aux attentes.'
                    ]
                ]
            ]
        ]);

        // Définir les champs du formulaire
        $champs = [
            [
                'ordre_affichage' => 1,
                'label' => 'Contexte et justification',
                'info' => '',
                'attribut' => 'contexte_justification',
                'placeholder' => 'Décrivez le contexte et la justification',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 3000,
                        'min_length' => 50
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 3000,
                        'min' => 50,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 2,
                'label' => 'Objectifs de la mission',
                'info' => '',
                'attribut' => 'objectifs_projet',
                'placeholder' => 'Définissez les objectifs',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 2500,
                        'min_length' => 30
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 2500,
                        'min' => 30,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 3,
                'label' => 'Résultats attendus de la mission',
                'info' => '',
                'attribut' => 'resultats_attendus',
                'placeholder' => 'Décrivez les résultats attendus',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 4,
                        'max_length' => 2500,
                        'min_length' => 30
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 2500,
                        'min' => 30,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 4,
                'label' => 'Etendue des services et activités à assurer',
                'info' => '',
                'attribut' => 'etendue_service',
                'placeholder' => 'Décrivez l\'étendue des services et activités à assurer',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 2,
                        'max_length' => 1500,
                        'min_length' => 20
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 1500,
                        'min' => 20,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 5,
                'label' => 'Mission du carbinet d\'étude',
                'info' => '',
                'attribut' => 'demarche_technique',
                'placeholder' => 'Décrivez la démarche technique',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 2,
                        'max_length' => 1500,
                        'min_length' => 20
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 1500,
                        'min' => 20,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 6,
                'label' => 'Profil du consultant (Cabinet, expert)',
                'info' => '',
                'attribut' => 'profil_consultant',
                'placeholder' => 'Decrivez le profil',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 2,
                        'max_length' => 1500,
                        'min_length' => 20
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 1500,
                        'min' => 20,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 7,
                'label' => 'Approche méthodologique',
                'info' => '',
                'attribut' => 'approche_methodologique',
                'placeholder' => 'Quels sont les livrables attendus',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 3,
                        'max_length' => 2000,
                        'min_length' => 30
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 2000,
                        'min' => 30,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 8,
                'label' => 'Calendrier des travaux, production et validation du rapport',
                'info' => '',
                'attribut' => 'calendrier_travaux',
                'placeholder' => 'Décrivez le pilotage et la gouvernance',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 3,
                        'max_length' => 2000,
                        'min_length' => 30
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 2000,
                        'min' => 30,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 9,
                'label' => 'Durée de la mission',
                'info' => '',
                'attribut' => 'duree_mission',
                'placeholder' => 'Precisez la duree d\'execution de la mission',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 2,
                        'max_length' => 1500,
                        'min_length' => 20
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 1500,
                        'min' => 20,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 10,
                'label' => 'Coût estimatif du projet',
                'info' => '',
                'attribut' => 'cout_estimatif',
                'placeholder' => 'Indiquez le coût estimatif global',
                'is_required' => true,
                'type_champ' => 'numeric',
                'meta_options' => [
                    'configs' => [
                        'rows' => 2,
                        'max_length' => 1500,
                        'min_length' => 20
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 1500,
                        'min' => 20,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 11,
                'label' => 'Budget estimatif et source de financement',
                'info' => '',
                'attribut' => 'budget_estimatif_source',
                'placeholder' => '',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 2,
                        'max_length' => 1500,
                        'min_length' => 20
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 1500,
                        'min' => 20,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ],
            [
                'ordre_affichage' => 12,
                'label' => 'Offres technique et financière (les critères d\'évaluation des offres) en cas de financement de l\'étude par un partenaire',
                'info' => '',
                'attribut' => 'offre_technique',
                'placeholder' => '',
                'is_required' => true,
                'type_champ' => 'textarea',
                'meta_options' => [
                    'configs' => [
                        'rows' => 3,
                        'max_length' => 2000,
                        'min_length' => 30
                    ],
                    'conditions' => [
                        'disable' => false,
                        'visible' => true,
                        'conditions' => []
                    ],
                    'validations_rules' => [
                        'max' => 2000,
                        'min' => 30,
                        'string' => true,
                        'required' => true
                    ]
                ]
            ]
        ];

        // Traiter les sections avec leurs champs
        if (!empty($champs)) {
            $this->createChamp($champs, $document);
        }

        $this->command->info('Canevas d\'appréciation des TDRs créé avec succès');
    }

    protected $documentData = [

        "nom" => "Canevas d'appréciation des termes de reference",
        "slug" => "canevas-appreciation-tdr",
        "description" => "Canevas d'appréciation des termes de reference",
        "type" => "formulaire",
        "sections" => [
            [
                "key" => "informations-générales",
                "intitule" => "Informations Générales",
                "description" => "Informations Générales",
                "ordre_affichage" => 1,
                "type" => "formulaire",
                "champs" => [
                    [
                        "label" => "Titre du projet",
                        "info" => "",
                        "key" => "titre_projet",
                        "attribut" => "titre_projet",
                        "placeholder" => "Saisissez le titre de votre projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "text",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 255,
                                "min_length" => 1
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Sigle du projet",
                        "info" => "",
                        "key" => "sigle",
                        "attribut" => "sigle",
                        "placeholder" => "Acronyme du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 2,
                        "type_champ" => "text",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 50,
                                "min_length" => 1
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Categorie de projet",
                        "info" => "",
                        "key" => "categorieId",
                        "attribut" => "categorieId",
                        "placeholder" => "Selectionnez la categorie de l'idée de projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 3,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => null,
                                "min_length" => 1
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Durée du projet",
                        "info" => "En Mois",
                        "key" => "duree",
                        "attribut" => "duree",
                        "placeholder" => "Ex: 24 mois",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 4,
                        "type_champ" => "number",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => null,
                                "min_length" => 1
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 0,
                                "array" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Coût en euro",
                        "info" => "",
                        "key" => "cout_euro",
                        "attribut" => "cout_euro",
                        "placeholder" => "0",
                        "is_required" => true,
                        "default_value" => "0",
                        "isEvaluated" => false,
                        "ordre_affichage" => 5,
                        "type_champ" => "number",
                        "meta_options" => [
                            "configs" => [
                                "max" => null,
                                "min" => 0,
                                "step" => 1
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 0,
                                "numeric" => 0,
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Coût en dollar canadien",
                        "info" => "",
                        "key" => "cout_dollar_canadien",
                        "attribut" => "cout_dollar_canadien",
                        "placeholder" => "0",
                        "is_required" => true,
                        "default_value" => "0",
                        "isEvaluated" => false,
                        "ordre_affichage" => 5,
                        "type_champ" => "number",
                        "meta_options" => [
                            "configs" => [
                                "max" => null,
                                "min" => 0,
                                "step" => 1
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 0,
                                "numeric" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Coût estimatif du projet",
                        "info" => "",
                        "key" => "cout_estimatif_projet",
                        "attribut" => "cout_estimatif_projet",
                        "placeholder" => "0",
                        "is_required" => true,
                        "default_value" => "0",
                        "isEvaluated" => false,
                        "ordre_affichage" => 5,
                        "type_champ" => "number",
                        "meta_options" => [
                            "configs" => [
                                "max" => null,
                                "min" => 0,
                                "step" => 1
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 2,
                                "array" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Coût en dollar americain",
                        "info" => "",
                        "key" => "cout_dollar_americain",
                        "attribut" => "cout_dollar_americain",
                        "placeholder" => "0",
                        "is_required" => true,
                        "default_value" => "0",
                        "isEvaluated" => false,
                        "ordre_affichage" => 5,
                        "type_champ" => "number",
                        "meta_options" => [
                            "configs" => [
                                "max" => null,
                                "min" => 0,
                                "step" => 1
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 0,
                                "numeric" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ]
                ]
            ],
            [
                "key" => "secteur-d-activite-et-localisation",
                "intitule" => "Secteur d'activité et Localisation",
                "description" => "Secteur d'activité et Localisation",
                "ordre_affichage" => 2,
                "type" => "formulaire",
                "champs" => [
                    [
                        "label" => "Grand Secteur",
                        "info" => "",
                        "key" => "grand_secteur",
                        "attribut" => "grand_secteur",
                        "placeholder" => "Choisissez un grand secteur",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => []
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Secteur",
                        "info" => "",
                        "key" => "secteur",
                        "attribut" => "secteur",
                        "placeholder" => "Choisissez un secteur",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 2,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => []
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Sous Secteur",
                        "info" => "",
                        "key" => "secteurId",
                        "attribut" => "secteurId",
                        "placeholder" => "Choisissez un sous secteur",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 3,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => []
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Départements",
                        "info" => "",
                        "key" => "departements",
                        "attribut" => "departements",
                        "placeholder" => "Choisissez un département",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 4,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 0,
                                "array" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Communes",
                        "info" => "",
                        "key" => "communes",
                        "attribut" => "communes",
                        "placeholder" => "Choisissez une commune",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 5,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 0,
                                "array" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Arrondissements",
                        "info" => "",
                        "key" => "arrondissements",
                        "attribut" => "arrondissements",
                        "placeholder" => "Choisissez un arrondissement",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 6,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 0,
                                "array" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Villages",
                        "info" => "",
                        "key" => "villages",
                        "attribut" => "villages",
                        "placeholder" => "Selectionnez les villages",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 7,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "multiple" => true,
                                "min_length" => 1
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "min" => 0,
                                "array" => true
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ]
                ]
            ],
            [
                "key" => "cadres-stratégiques",
                "intitule" => "Cadres stratégiques",
                "description" => "Cadres stratégiques",
                "ordre_affichage" => 3,
                "type" => "formulaire",
                "champs" => [
                    [
                        "label" => "Objectifs de developpement durable",
                        "info" => "",
                        "key" => "odds",
                        "attribut" => "odds",
                        "placeholder" => "Sélectionnez un ODD",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Cibles des odds",
                        "info" => "",
                        "key" => "cibles",
                        "attribut" => "cibles",
                        "placeholder" => "Sélectionnez les cibles",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 2,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Orientations stratégique du PND",
                        "info" => "",
                        "key" => "orientations_strategiques",
                        "attribut" => "orientations_strategiques",
                        "placeholder" => "Choisissez une orientation",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 3,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Objectifs stratégique du PND",
                        "info" => "",
                        "key" => "objectifs_strategiques",
                        "attribut" => "objectifs_strategiques",
                        "placeholder" => "Choisissez un objectif",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 4,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Résultats stratégique du PND",
                        "info" => "",
                        "key" => "resultats_strategiques",
                        "attribut" => "resultats_strategiques",
                        "placeholder" => "Choisissez un résultat",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 5,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Piliers du PAG",
                        "info" => "",
                        "key" => "piliers_pag",
                        "attribut" => "piliers_pag",
                        "placeholder" => "Choisissez les piliers",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 7,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Axes du PAG",
                        "info" => "",
                        "key" => "axes_pag",
                        "attribut" => "axes_pag",
                        "placeholder" => "Choisissez les axes du pags",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 8,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Actions du PAG",
                        "info" => "",
                        "key" => "actions_pag",
                        "attribut" => "actions_pag",
                        "placeholder" => "Choisissez une action",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 9,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ]
                ]
            ],
            [
                "key" => "financement-et-bénéficiaires",
                "intitule" => "Financement et Bénéficiaires",
                "description" => "Financement et Bénéficiaires",
                "ordre_affichage" => 4,
                "type" => "formulaire",
                "champs" => [
                    [
                        "label" => "Types de financement",
                        "info" => "",
                        "key" => "types_financement",
                        "attribut" => "types_financement",
                        "placeholder" => "Choisissez un type",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Natures du financement",
                        "info" => "",
                        "key" => "natures_financement",
                        "attribut" => "natures_financement",
                        "placeholder" => "Choisissez une nature",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 2,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Sources de financement",
                        "info" => "",
                        "key" => "sources_financement",
                        "attribut" => "sources_financement",
                        "placeholder" => "Choisissez une source",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 3,
                        "type_champ" => "select",
                        "meta_options" => [
                            "configs" => [
                                "options" => [],
                                "multiple" => true
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Public cible",
                        "info" => "",
                        "key" => "public_cible",
                        "attribut" => "public_cible",
                        "placeholder" => "Décrivez le public cible du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 4,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1000,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1000,
                                "min" => 10
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Constats majeurs",
                        "info" => "",
                        "key" => "constats_majeurs",
                        "attribut" => "constats_majeurs",
                        "placeholder" => "",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 5,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1000,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1000,
                                "min" => 10
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Parties prenantes",
                        "info" => "",
                        "key" => "parties_prenantes",
                        "attribut" => "parties_prenantes",
                        "placeholder" => "Identifiez les parties prenantes impliquées",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 6,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1000,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ]
                ]
            ],
            [
                "key" => "contexte-et-analyse",
                "intitule" => "Contexte et Analyse",
                "description" => "Contexte et Analyse",
                "ordre_affichage" => 5,
                "type" => "formulaire",
                "champs" => [
                    [
                        "label" => "Objectif du projet",
                        "info" => "",
                        "key" => "objectif_general",
                        "attribut" => "objectif_general",
                        "placeholder" => "Décrivez l'objectif principal du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "min_length" => 20
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 2000,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Objectif Specifiques",
                        "info" => "",
                        "key" => "objectifs_specifiques",
                        "attribut" => "objectifs_specifiques",
                        "placeholder" => "Décrivez l'objectif principal du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "min_length" => 20
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Résultats attendus",
                        "info" => "",
                        "key" => "resultats_attendus",
                        "attribut" => "resultats_attendus",
                        "placeholder" => "Décrivez les résultats attendus",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 2,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "min_length" => 20
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "array" => true,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Origine du projet",
                        "info" => "",
                        "key" => "origine",
                        "attribut" => "origine",
                        "placeholder" => "D'où vient l'idée de ce projet ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 3,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 20
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1500,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Fondement du projet",
                        "info" => "",
                        "key" => "fondement",
                        "attribut" => "fondement",
                        "placeholder" => "Sur quoi se base ce projet ?",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 4,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 20
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1500,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Situation actuelle",
                        "info" => "",
                        "key" => "situation_actuelle",
                        "attribut" => "situation_actuelle",
                        "placeholder" => "Décrivez la situation actuelle",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 5,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "min_length" => 20
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 2000,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Situation désirée",
                        "info" => "",
                        "key" => "situation_desiree",
                        "attribut" => "situation_desiree",
                        "placeholder" => "Décrivez la situation visée",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 6,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "min_length" => 20
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 2000,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Contraintes",
                        "info" => "",
                        "key" => "contraintes",
                        "attribut" => "contraintes",
                        "placeholder" => "Identifiez les principales contraintes",
                        "is_required" => false,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 7,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1000,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 2000,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ]
                ]
            ],
            [
                "key" => "description-technique-et-impacts",
                "intitule" => "Description technique et Impacts",
                "description" => "Description technique et Impacts",
                "ordre_affichage" => 6,
                "type" => "formulaire",
                "champs" => [
                    [
                        "label" => "Description du projet",
                        "info" => "",
                        "key" => "description_projet",
                        "attribut" => "description_projet",
                        "placeholder" => "Description détaillée du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 3000,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Description du projet",
                        "info" => "",
                        "key" => "description_extrants",
                        "attribut" => "description_extrants",
                        "placeholder" => "Description détaillée du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 3000,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Échéancier du projet",
                        "info" => "",
                        "key" => "echeancier",
                        "attribut" => "echeancier",
                        "placeholder" => "Description détaillée du projet",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 3000,
                                "min_length" => 50
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 3000,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Caractéristiques techniques",
                        "info" => "",
                        "key" => "caracteristiques_techniques",
                        "attribut" => "caracteristiques_techniques",
                        "placeholder" => "Caractéristiques techniques",
                        "is_required" => false,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 2,
                        "type_champ" => "textarea",
                        "sectionId" => 43,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 2000,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Impact environnemental",
                        "info" => "",
                        "key" => "impact_environnement",
                        "attribut" => "impact_environnement",
                        "placeholder" => "Impact sur l'environnement",
                        "is_required" => false,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 3,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1500,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Estimation des coûts et benefices",
                        "info" => "",
                        "key" => "estimation_couts",
                        "attribut" => "estimation_couts",
                        "placeholder" => "",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 4,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1500,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Aspects organisationnels",
                        "info" => "",
                        "key" => "aspect_organisationnel",
                        "attribut" => "aspect_organisationnel",
                        "placeholder" => "",
                        "is_required" => false,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 4,
                        "type_champ" => "textarea",
                        "sectionId" => 43,
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1500,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Risques immédiats",
                        "info" => "",
                        "key" => "risques_immediats",
                        "attribut" => "risques_immediats",
                        "placeholder" => "Risques identifiés",
                        "is_required" => false,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 5,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1500,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Description sommaire",
                        "info" => "",
                        "key" => "sommaire",
                        "attribut" => "sommaire",
                        "placeholder" => "Description sommaire",
                        "is_required" => false,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 6,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1500,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Autre solutions alternatives considere et non retenues",
                        "info" => "",
                        "key" => "description",
                        "attribut" => "description",
                        "placeholder" => "Autre solutions alternatives",
                        "is_required" => false,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 6,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1500,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Conclusions",
                        "info" => "",
                        "key" => "conclusions",
                        "attribut" => "conclusions",
                        "placeholder" => "Conclusions générales",
                        "is_required" => false,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 6,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 10
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => false,
                                "string" => true,
                                "max" => 1500,
                                "min" => 0
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ]
                ]
            ]
        ]
    ];

    /**
     * Créer un champ avec validation des données
     */
    private function createChamp(array $champData, $document, $section = null): void
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
            //'commentaire' => isset($champData['commentaire']) ? $champData['commentaire'] : null,
            'ordre_affichage' => $champData['ordre_affichage'],
            'type_champ' => $champData['type_champ'],
            'meta_options' => $champData['meta_options'] ?? [],
            'documentId' => $document->id,
            'sectionId' => $section ? $section->id : null
        ];

        // Créer le champ via la relation appropriée
        if ($section) {
            $section->champs()->create($champAttributes);
        } else {
            $document->champs()->create($champAttributes);
        }
    }
}
