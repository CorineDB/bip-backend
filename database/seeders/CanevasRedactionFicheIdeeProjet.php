<?php

namespace Database\Seeders;

use App\Models\CategorieProjet;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanevasRedactionFicheIdeeProjet extends Seeder
{

    protected $documentData = [

        "nom" => "Fiche de remplissage d'une idée de projet",
        "slug" => "fiche-idee",
        "description" => "Formulaire de rédaction d'une idée de projet",
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
                        "label" => "Echéancier des principaux extrants",
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
     * Run the database seeds.
     */
    public function run(): void
    {
        /* DB::table('champs')->truncate();
        DB::table('champs_sections')->truncate();
        DB::table('documents')->truncate();
        DB::table('categories_document')->truncate(); */
        $categorieDocument = \App\Models\CategorieDocument::firstOrCreate([
            'slug' => "fiche-idee",
        ], [
            'nom' => "Canevas standardise d'ideation de projet",
            'slug' => "fiche-idee",
            "description" => "Formulaire standard d'ideation de projet",
            "format" => "document"
        ]);

        // Mode création
        // Extraire les données relationnelles avant création
        $sectionsData = $this->documentData['sections'] ?? [];

        // Nettoyer les données du document principal
        $documentData = collect($this->documentData)->except(['sections', 'champs', 'id'])->toArray();

        $documentData = array_merge($documentData, [
            "categorieId" => $categorieDocument->id
        ]);

        // Créer le document principal
        $document = Document::updateOrCreate(['slug' => "fiche-idee"], $documentData);

        // Traiter les sections avec leurs champs
        if (!empty($sectionsData)) {
            $this->createSectionsWithChamps($document, $sectionsData);
        }
    }



    /**
     * Créer les sections avec leurs champs associés
     */
    private function createSectionsWithChamps($document, array $sectionsData): void
    {
        foreach ($sectionsData as $sectionData) {
            $section = $document->sections()->create([
                'intitule' => $sectionData['intitule'],
                'description' => $sectionData['description'],
                'ordre_affichage' => $sectionData['ordre_affichage'],
                'type' => $sectionData['type'] ?? null
            ]);

            // Créer les champs de cette section si fournis
            if (isset($sectionData['champs']) && is_array($sectionData['champs'])) {
                foreach ($sectionData['champs'] as $champData) {
                    $this->createChamp($champData, $document, $section);
                }
            }
        }
    }

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
