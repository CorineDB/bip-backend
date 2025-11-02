<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use App\Models\ChampSection;
use App\Models\Champ;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanevasRedactionFicheIdeeProjet extends Seeder
{
    protected $documentData = [
        'nom' => 'Fiche de remplissage d\'une idée de projet',
        'slug' => 'fiche-idee',
        'description' => 'Formulaire de rédaction d\'une idée de projet',
        'type' => 'formulaire',
        'sections' => [
            [
                'key' => 'informations-générales',
                'intitule' => 'Origine du projet',
                'description' => 'Informations Générales',
                'ordre_affichage' => 1,
                'type' => 'formulaire',
                'champs' => [
                    [
                        'label' => 'Titre du projet',
                        'info' => '',
                        'key' => 'titre_projet',
                        'attribut' => 'titre_projet',
                        'placeholder' => 'Saisissez le titre de votre projet',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 1,
                        'type_champ' => 'text',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => 255,
                                'min_length' => 1,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => true,
                    ],
                    [
                        'label' => 'Sigle du projet',
                        'info' => '',
                        'key' => 'sigle',
                        'attribut' => 'sigle',
                        'placeholder' => 'Acronyme du projet',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 2,
                        'type_champ' => 'text',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => 50,
                                'min_length' => 1,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Code du projet',
                        'info' => '',
                        'key' => 'identifiant_bip',
                        'attribut' => 'identifiant_bip',
                        'placeholder' => 'Identifiant du projet dans BIP',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 2,
                        'type_champ' => 'text',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => 50,
                                'min_length' => 1,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Categorie de projet',
                        'info' => '',
                        'key' => 'categorieId',
                        'attribut' => 'categorieId',
                        'placeholder' => 'Selectionnez la categorie de l\'idée de projet',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 3,
                        'type_champ' => 'select',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => NULL,
                                'min_length' => 1,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => true,
                    ],
                    [
                        'label' => 'Origine du projet',
                        'info' => '',
                        'key' => 'origine',
                        'attribut' => 'origine',
                        'placeholder' => 'D\'où vient l\'idée de ce projet ?',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 4,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => 1500,
                                'min_length' => 20,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                ],
                'sous_sections' => [
                    [
                        'key' => 'Fondement',
                        'intitule' => 'Fondement',
                        'description' => 'Action de la stratégie/Plan/Programme',
                        'ordre_affichage' => 10,
                        'type' => 'formulaire',
                        'champs' => [
                            [
                                'label' => 'Objectifs de developpement durable',
                                'info' => '',
                                'key' => 'odds',
                                'attribut' => 'odds',
                                'placeholder' => 'Sélectionnez un ODD',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 1,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Cibles des odds',
                                'info' => '',
                                'key' => 'cibles',
                                'attribut' => 'cibles',
                                'placeholder' => 'Sélectionnez les cibles',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 2,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                        ],
                    ],
                    [
                        'key' => 'Plan National de Developpement',
                        'intitule' => 'Plan National de Developpement',
                        'description' => 'Coherence avec Plan National de developpement',
                        'ordre_affichage' => 11,
                        'type' => 'formulaire',
                        'champs' => [
                            [
                                'label' => 'Orientations stratégique du PND',
                                'info' => '',
                                'key' => 'orientations_strategiques',
                                'attribut' => 'orientations_strategiques',
                                'placeholder' => 'Choisissez une orientation',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 1,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Objectifs stratégique du PND',
                                'info' => '',
                                'key' => 'objectifs_strategiques',
                                'attribut' => 'objectifs_strategiques',
                                'placeholder' => 'Choisissez un objectif',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 2,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Résultats stratégique du PND',
                                'info' => '',
                                'key' => 'resultats_strategiques',
                                'attribut' => 'resultats_strategiques',
                                'placeholder' => 'Choisissez un résultat',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 3,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                        ],
                    ],
                    [
                        'key' => 'Programme d\'Action du Gouvernement',
                        'intitule' => 'Programme d\'Action du Gouvernement',
                        'description' => 'Alignement au programme d\'action du Gouvernement',
                        'ordre_affichage' => 12,
                        'type' => 'formulaire',
                        'champs' => [
                            [
                                'label' => 'Piliers du PAG',
                                'info' => '',
                                'key' => 'piliers_pag',
                                'attribut' => 'piliers_pag',
                                'placeholder' => 'Choisissez les piliers',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 1,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Axes du PAG',
                                'info' => '',
                                'key' => 'axes_pag',
                                'attribut' => 'axes_pag',
                                'placeholder' => 'Choisissez les axes du pags',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 2,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Actions du PAG',
                                'info' => '',
                                'key' => 'actions_pag',
                                'attribut' => 'actions_pag',
                                'placeholder' => 'Choisissez une action',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 3,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                        ],
                    ],
                    [
                        'key' => '',
                        'intitule' => 'Autres',
                        'description' => ' ',
                        'ordre_affichage' => 13,
                        'type' => 'formulaire',
                        'champs' => [
                            [
                                'label' => 'Situation actuelle',
                                'info' => 'Problématique et/ou besoins',
                                'key' => 'situation_actuelle',
                                'attribut' => 'situation_actuelle',
                                'placeholder' => 'Décrivez la situation actuelle',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 1,
                                'type_champ' => 'textarea',
                                'meta_options' => [
                                    'configs' => [
                                        'max_length' => 2000,
                                        'min_length' => 20,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Situation désirée',
                                'info' => 'Finalité, Buts',
                                'key' => 'situation_desiree',
                                'attribut' => 'situation_desiree',
                                'placeholder' => 'Décrivez la situation visée',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 2,
                                'type_champ' => 'textarea',
                                'meta_options' => [
                                    'configs' => [
                                        'max_length' => 2000,
                                        'min_length' => 20,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Contraintes à respecter et gérer',
                                'info' => '',
                                'key' => 'contraintes',
                                'attribut' => 'contraintes',
                                'placeholder' => 'Identifiez les principales contraintes',
                                'is_required' => false,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 3,
                                'type_champ' => 'textarea',
                                'meta_options' => [
                                    'configs' => [
                                        'max_length' => 1000,
                                        'min_length' => 10,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'secteur-d\'activité-et-localisation',
                'intitule' => 'Description sommaire de l’idée de projet',
                'description' => 'Description sommaire de l’idée de projet',
                'ordre_affichage' => 2,
                'type' => 'formulaire',
                'champs' => [
                    [
                        'label' => 'Description générale du projet',
                        'info' => '(Contexte & objectifs]',
                        'key' => 'description_projet',
                        'attribut' => 'description_projet',
                        'placeholder' => 'Description détaillée du projet',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 1,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 3,
                                'max_length' => 3000,
                                'min_length' => 50,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'max' => 3000,
                                'min' => 50,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Objectif Specifiques',
                        'info' => '',
                        'key' => 'objectifs_specifiques',
                        'attribut' => 'objectifs_specifiques',
                        'placeholder' => 'Décrivez l\'objectif principal du projet',
                        'is_required' => false,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 2,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 3,
                                'max_length' => 5000,
                                'min_length' => 20,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Durée du projet',
                        'info' => 'En Annee',
                        'key' => 'duree',
                        'attribut' => 'duree',
                        'placeholder' => 'Ex: 5 ans',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 3,
                        'type_champ' => 'number',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => NULL,
                                'min_length' => 1,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Echéancier des principaux extrants',
                        'info' => '(Indicateurs de réalisations physiques]',
                        'key' => 'echeancier',
                        'attribut' => 'echeancier',
                        'placeholder' => '',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 4,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 3,
                                'max_length' => 3000,
                                'min_length' => 50,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'max' => 3000,
                                'min' => 50,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Description des principaux extrants',
                        'info' => '(spécifications techniques]',
                        'key' => 'description_extrants',
                        'attribut' => 'description_extrants',
                        'placeholder' => 'Description détaillée des principaux extrants du projet',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 5,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 3,
                                'max_length' => 3000,
                                'min_length' => 50,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'max' => 3000,
                                'min' => 50,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Caractéristiques techniques',
                        'info' => '',
                        'key' => 'caracteristiques_techniques',
                        'attribut' => 'caracteristiques_techniques',
                        'placeholder' => 'Caractéristiques techniques',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 6,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 3,
                                'max_length' => 2000,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'max' => 2000,
                                'min' => 10,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Résultats attendus',
                        'info' => '',
                        'key' => 'resultats_attendus',
                        'attribut' => 'resultats_attendus',
                        'placeholder' => 'Décrivez les résultats attendus',
                        'is_required' => false,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 7,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 2,
                                'max_length' => 2000,
                                'min_length' => 20,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Impact environnemental',
                        'info' => '',
                        'key' => 'impact_environnement',
                        'attribut' => 'impact_environnement',
                        'placeholder' => 'Impact sur l\'environnement',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 8,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 3,
                                'max_length' => 1500,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'max' => 1500,
                                'min' => 10,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Aspects organisationnels',
                        'info' => '',
                        'key' => 'aspect_organisationnel',
                        'attribut' => 'aspect_organisationnel',
                        'placeholder' => '',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 9,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 3,
                                'max_length' => 1500,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'max' => 1500,
                                'min' => 10,
                                'required' => true,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Estimation des coûts et benefices',
                        'info' => '',
                        'key' => 'estimation_couts',
                        'attribut' => 'estimation_couts',
                        'placeholder' => '',
                        'is_required' => true,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 10,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 3,
                                'max_length' => 1500,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'max' => 1500,
                                'min' => 10,
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Risques immédiats',
                        'info' => '',
                        'key' => 'risques_immediats',
                        'attribut' => 'risques_immediats',
                        'placeholder' => 'Risques identifiés',
                        'is_required' => false,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 11,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'rows' => 2,
                                'max_length' => 1500,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'max' => 1500,
                                'min' => 10,
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Autre solutions alternatives considere et non retenues',
                        'info' => '',
                        'key' => 'description',
                        'attribut' => 'description',
                        'placeholder' => 'Autre solutions alternatives',
                        'is_required' => false,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 12,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => 1500,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Conclusions',
                        'info' => '',
                        'key' => 'conclusions',
                        'attribut' => 'conclusions',
                        'placeholder' => 'Conclusions générales',
                        'is_required' => false,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 13,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => 1500,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Description sommaire',
                        'info' => '',
                        'key' => 'sommaire',
                        'attribut' => 'sommaire',
                        'placeholder' => 'Description sommaire',
                        'is_required' => false,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 14,
                        'type_champ' => 'textarea',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => 1500,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Demandeur',
                        'info' => 'Porteur de projet',
                        'key' => 'demandeur',
                        'attribut' => 'demandeur',
                        'placeholder' => 'Porteur de projet',
                        'is_required' => false,
                        'default_value' => NULL,
                        'isEvaluated' => false,
                        'ordre_affichage' => 15,
                        'type_champ' => 'text',
                        'meta_options' => [
                            'configs' => [
                                'max_length' => 1500,
                                'min_length' => 10,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'min' => 0,
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                ],
                'sous_sections' => [
                    [
                        'key' => 'Secteur',
                        'intitule' => 'Secteur d\'intervention',
                        'description' => 'Entites Thematique',
                        'ordre_affichage' => 1,
                        'type' => 'formulaire',
                        'champs' => [
                            [
                                'label' => 'Grand Secteur',
                                'info' => '',
                                'key' => 'grand_secteur',
                                'attribut' => 'grand_secteur',
                                'placeholder' => 'Choisissez un grand secteur',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 1,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Secteur',
                                'info' => '',
                                'key' => 'secteur',
                                'attribut' => 'secteur',
                                'placeholder' => 'Choisissez un secteur',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 2,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Sous Secteur',
                                'info' => '',
                                'key' => 'secteurId',
                                'attribut' => 'secteurId',
                                'placeholder' => 'Choisissez un sous secteur',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 3,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                        ],
                    ],
                    [
                        'key' => 'Localisation',
                        'intitule' => 'Zone d\'intervention',
                        'description' => 'Localisation',
                        'ordre_affichage' => 2,
                        'type' => 'formulaire',
                        'champs' => [
                            [
                                'label' => 'Départements',
                                'info' => '',
                                'key' => 'departements',
                                'attribut' => 'departements',
                                'placeholder' => 'Choisissez un département',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 1,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'min' => 0,
                                        'array' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Communes',
                                'info' => '',
                                'key' => 'communes',
                                'attribut' => 'communes',
                                'placeholder' => 'Choisissez une commune',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 2,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'min' => 0,
                                        'array' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Arrondissements',
                                'info' => '',
                                'key' => 'arrondissements',
                                'attribut' => 'arrondissements',
                                'placeholder' => 'Choisissez un arrondissement',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 3,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'min' => 0,
                                        'array' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Villages',
                                'info' => '',
                                'key' => 'villages',
                                'attribut' => 'villages',
                                'placeholder' => 'Selectionnez les villages',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 4,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'multiple' => true,
                                        'min_length' => 1,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'min' => 0,
                                        'array' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                        ],
                    ],
                ],
            ],
            [
                'key' => 'financement-et-bénéficiaires',
                'intitule' => 'Financement et Bénéficiaires',
                'description' => 'Financement et Bénéficiaires',
                'ordre_affichage' => 3,
                'type' => 'formulaire',
                'champs' => [
                    [
                        'label' => 'Coût estimatif du projet',
                        'info' => '',
                        'key' => 'cout_estimatif_projet',
                        'attribut' => 'cout_estimatif_projet',
                        'placeholder' => '0',
                        'is_required' => true,
                        'default_value' => 0,
                        'isEvaluated' => false,
                        'ordre_affichage' => 1,
                        'type_champ' => 'number',
                        'meta_options' => [
                            'configs' => [
                                'max' => NULL,
                                'min' => 0,
                                'step' => 1,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Coût en dollar canadien',
                        'info' => '',
                        'key' => 'cout_dollar_canadien',
                        'attribut' => 'cout_dollar_canadien',
                        'placeholder' => '0',
                        'is_required' => true,
                        'default_value' => 0,
                        'isEvaluated' => false,
                        'ordre_affichage' => 2,
                        'type_champ' => 'number',
                        'meta_options' => [
                            'configs' => [
                                'max' => NULL,
                                'min' => 0,
                                'step' => 1,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Coût en dollar americain',
                        'info' => '',
                        'key' => 'cout_dollar_americain',
                        'attribut' => 'cout_dollar_americain',
                        'placeholder' => '0',
                        'is_required' => true,
                        'default_value' => 0,
                        'isEvaluated' => false,
                        'ordre_affichage' => 3,
                        'type_champ' => 'number',
                        'meta_options' => [
                            'configs' => [
                                'max' => NULL,
                                'min' => 0,
                                'step' => 1,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                    [
                        'label' => 'Coût en euro',
                        'info' => '',
                        'key' => 'cout_euro',
                        'attribut' => 'cout_euro',
                        'placeholder' => '0',
                        'is_required' => true,
                        'default_value' => 0,
                        'isEvaluated' => false,
                        'ordre_affichage' => 4,
                        'type_champ' => 'number',
                        'meta_options' => [
                            'configs' => [
                                'max' => NULL,
                                'min' => 0,
                                'step' => 1,
                            ],
                            'conditions' => [
                                'disable' => false,
                                'visible' => true,
                                'conditions' => [],
                            ],
                            'validations_rules' => [
                                'required' => false,
                            ],
                        ],
                        'champ_standard' => true,
                        'startWithNewLine' => false,
                    ],
                ],
                'sous_sections' => [
                    [
                        'key' => 'Financements',
                        'intitule' => 'Financements',
                        'description' => 'Financements',
                        'ordre_affichage' => 1,
                        'type' => 'formulaire',
                        'champs' => [
                            [
                                'label' => 'Types de financement',
                                'info' => '',
                                'key' => 'types_financement',
                                'attribut' => 'types_financement',
                                'placeholder' => 'Choisissez un type',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 1,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'min' => 0,
                                        'array' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Natures du financement',
                                'info' => '',
                                'key' => 'natures_financement',
                                'attribut' => 'natures_financement',
                                'placeholder' => 'Choisissez une nature',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 2,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'min' => 0,
                                        'array' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Sources de financement',
                                'info' => '',
                                'key' => 'sources_financement',
                                'attribut' => 'sources_financement',
                                'placeholder' => 'Choisissez une source',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 3,
                                'type_champ' => 'select',
                                'meta_options' => [
                                    'configs' => [
                                        'options' => [],
                                        'multiple' => true,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'min' => 0,
                                        'array' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                        ],
                    ],
                    [
                        'key' => 'Beneficiaires',
                        'intitule' => 'Beneficiaires',
                        'description' => 'Beneficiaires',
                        'ordre_affichage' => 2,
                        'type' => 'formulaire',
                        'champs' => [
                            [
                                'label' => 'Public cible',
                                'info' => '',
                                'key' => 'public_cible',
                                'attribut' => 'public_cible',
                                'placeholder' => 'Décrivez le public cible du projet',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 1,
                                'type_champ' => 'textarea',
                                'meta_options' => [
                                    'configs' => [
                                        'max_length' => 1000,
                                        'min_length' => 10,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'max' => 1000,
                                        'min' => 10,
                                        'string' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Constats majeurs',
                                'info' => '',
                                'key' => 'constats_majeurs',
                                'attribut' => 'constats_majeurs',
                                'placeholder' => '',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 2,
                                'type_champ' => 'textarea',
                                'meta_options' => [
                                    'configs' => [
                                        'max_length' => 1000,
                                        'min_length' => 10,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'max' => 1000,
                                        'min' => 10,
                                        'string' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                            [
                                'label' => 'Parties prenantes',
                                'info' => '',
                                'key' => 'parties_prenantes',
                                'attribut' => 'parties_prenantes',
                                'placeholder' => 'Identifiez les parties prenantes impliquées',
                                'is_required' => true,
                                'default_value' => NULL,
                                'isEvaluated' => false,
                                'ordre_affichage' => 3,
                                'type_champ' => 'textarea',
                                'meta_options' => [
                                    'configs' => [
                                        'max_length' => 1000,
                                        'min_length' => 10,
                                    ],
                                    'conditions' => [
                                        'disable' => false,
                                        'visible' => true,
                                        'conditions' => [],
                                    ],
                                    'validations_rules' => [
                                        'min' => 0,
                                        'array' => true,
                                        'required' => false,
                                    ],
                                ],
                                'champ_standard' => true,
                                'startWithNewLine' => false,
                            ],
                        ],
                    ],
                ],
            ]
        ]
    ];

    public function run(): void
    {
        DB::beginTransaction();

        try {
            $categorieDocument = CategorieDocument::updateOrCreate([
                'slug' => 'fiche-idee'
            ], [
                'nom' => "Fiche idee",
                'slug' => 'fiche-idee',
                'format' => 'formulaire'
            ]);

            $sectionsData = $this->documentData['sections'] ?? [];
            $champsData = $this->documentData['champs'] ?? [];
            $documentData = collect($this->documentData)->except(['sections', 'champs', 'id', 'categorie'])->toArray();
            $documentData = array_merge($documentData, ["categorieId" => $categorieDocument->id]);

            //$categorieDocument->documents->each->forceDelete();
            $document = Document::updateOrCreate(['nom' => $documentData['nom']], $documentData);

            // Créer les sections
            if (!empty($sectionsData)) {
                foreach ($sectionsData as $sectionData) {
                    $this->createSection($sectionData, $document, null);
                }
            }

            // Créer les champs directs (sans section)
            if (!empty($champsData)) {
                foreach ($champsData as $champData) {
                    $this->createChamp($champData, $document, null);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    private function createSection(array $sectionData, $document, $parentSection = null): void
    {
        $sectionAttributes = [
            'intitule' => $sectionData['intitule'],
            'slug' => $sectionData['key'] ?? $sectionData['slug'] ?? null,
            'description' => $sectionData['description'] ?? null,
            'documentId' => $document->id,
            'parentSectionId' => $parentSection ? $parentSection->id : null,
            'ordre_affichage' => $sectionData['ordre_affichage'],
        ];

        $section = ChampSection::updateOrCreate([
            'intitule' => $sectionData['intitule'],
            'documentId' => $document->id,
            'parentSectionId' => $parentSection ? $parentSection->id : null
        ], $sectionAttributes);

        // Créer les sous-sections
        if (isset($sectionData['sous_sections']) && !empty($sectionData['sous_sections'])) {
            foreach ($sectionData['sous_sections'] as $sousSection) {
                $this->createSection($sousSection, $document, $section);
            }
        }

        // Créer les champs de la section
        if (isset($sectionData['champs']) && !empty($sectionData['champs'])) {
            foreach ($sectionData['champs'] as $champData) {
                $this->createChamp($champData, $document, $section);
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

        Champ::updateOrCreate([
            'attribut' => $champData['attribut'],
            'sectionId' => $parentSection ? $parentSection->id : null,
            'documentId' => $document->id
        ], $champAttributes);
    }
}
