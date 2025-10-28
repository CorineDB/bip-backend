<?php

namespace Database\Seeders;

use App\Models\CategorieDocument;
use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistEtudeFaisabiliteOrganisationnelleJuridiqueSeeder extends Seeder
{
    protected $documentData = [
  'nom' => 'Check liste de suivi d\'étude de faisabilité organisationnelle et juridique',
  'slug' => 'check-liste-de-suivi-etude-de-faisabilite-organisationnelle-juridique',
  'description' => 'Check liste de suivi d\'étude de faisabilité organisationnelle et juridiques',
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
      'element_type' => 'section',
      'ordre_affichage' => 1,
      'key' => 'section-validation-pertinence-structure-organisationnelle',
      'intitule' => 'Validation de la pertinence de la structure organisationnelle adoptée pour réaliser le projet',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Contexte organisationnel',
          'info' => '',
          'key' => 'contexte_organisationnel',
          'attribut' => 'contexte_organisationnel',
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
          'label' => 'Structure organisationnelle',
          'info' => '',
          'key' => 'structure_organisationnel',
          'attribut' => 'structure_organisationnel',
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
          'label' => 'Ajustements & stratégie',
          'info' => '',
          'key' => 'ajustements_stratégie',
          'attribut' => 'ajustements_stratégie',
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
          'label' => 'Définition des aspects organisationnels et de la phase de mise en œuvre du projet',
          'info' => '',
          'key' => 'definition_aspects_organisationnels_phase_mise_en_oeuvre',
          'attribut' => 'definition_aspects_organisationnels_phase_mise_en_oeuvre',
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
    ],
      [
      'element_type' => 'section',
      'ordre_affichage' => 2,
      'key' => 'section-definition-verification-profil-gestionnaire',
      'intitule' => 'Définition et vérification du profil du gestionnaire du projet',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Compétences souhaitées',
          'info' => '',
          'key' => 'competences_souhaitees',
          'attribut' => 'competences_souhaitees',
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
          'label' => 'Faiblesses et les mesures correctrices',
          'info' => '',
          'key' => 'faiblesses_mesures_correctrices',
          'attribut' => 'faiblesses_mesures_correctrices',
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
    ],
      [
      'element_type' => 'section',
      'ordre_affichage' => 3,
      'key' => 'section-definition-strategie-gestion-impact-1',
      'intitule' => 'Définition et validation du cadre institutionnel et la composition de l’équipe de projet et son mode de fonctionnement',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Vérification de la composition de l’équipe',
          'info' => '',
          'key' => 'verification_composition_equipe',
          'attribut' => 'verification_composition_equipe',
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
          'label' => 'Fonctionnement actuel des équipes',
          'info' => '',
          'key' => 'fonctionnement_actuel',
          'attribut' => 'fonctionnement_actuel',
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
    ],
      [
      'element_type' => 'section',
      'ordre_affichage' => 4,
      'key' => 'section-confirmation-ajustement',
      'intitule' => 'Confirmation et/ou ajustement de l’exhaustivité et de la disponibilité des ressources nécessaires pour la préparation, l’exécution et l’exploitation du projet',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Ressources à réaliser',
          'info' => '',
          'key' => 'ressources_a_realiser',
          'attribut' => 'ressources_a_realiser',
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
          'label' => 'Explication des ressources',
          'info' => '',
          'key' => 'explication_des_ressources',
          'attribut' => 'explication_des_ressources',
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
          'label' => 'Programmation et négociation',
          'info' => '',
          'key' => 'programmation_negociation',
          'attribut' => 'programmation_negociation',
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
    ],
      [
      'element_type' => 'section',
      'ordre_affichage' => 5,
      'key' => 'section-confirmation-ajustement-disponibilite',
      'intitule' => 'Confirmation et/ou ajustement de l’exhaustivité et de la disponibilité des ressources nécessaires pour la préparation, l’exécution et l’exploitation du projet.',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Inventaire des impacts',
          'info' => '',
          'key' => 'inventaire_impacts',
          'attribut' => 'inventaire_impacts',
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
          'label' => 'Théorie de changement du projet',
          'info' => '',
          'key' => 'theorie_changement',
          'attribut' => 'theorie_changement',
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
          'label' => 'Leviers et les obstacles clés',
          'info' => '',
          'key' => 'leviers_obstacles',
          'attribut' => 'leviers_obstacles',
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
          'label' => 'Impact de l’Etat et ses partenaires',
          'info' => '',
          'key' => 'impact_etat_partenaires',
          'attribut' => 'impact_etat_partenaires',
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
          'ordre_affichage' => 5,
          'label' => 'Force d’action du projet',
          'info' => '',
          'key' => 'force_action',
          'attribut' => 'force_action',
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
          'ordre_affichage' => 6,
          'label' => 'Stratégie de changement',
          'info' => '',
          'key' => 'strategie_changement',
          'attribut' => 'strategie_changement',
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
    ],
      [
      'element_type' => 'section',
      'ordre_affichage' => 6,
      'key' => 'section-revue-verification-politique',
      'intitule' => 'Revue et vérification de la politique de gestion',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Inventaire des impacts',
          'info' => '',
          'key' => 'inventaire_des_impacts',
          'attribut' => 'inventaire_des_impacts',
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
          'label' => 'Commentaire sur l’exhaustivité de la politique de qualité',
          'info' => '',
          'key' => 'commentaire_exhaustivite_politique_qualite',
          'attribut' => 'commentaire_exhaustivite_politique_qualite',
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
          'label' => 'Stratégie d’ajustement et de correction des lacunes',
          'info' => '',
          'key' => 'strategie_ajustement_correction_lacunes',
          'attribut' => 'strategie_ajustement_correction_lacunes',
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
    ],
      [
      'element_type' => 'section',
      'ordre_affichage' => 7,
      'key' => 'section-assurance-pertinence-mecanismes-outils-suivi-controle',
      'intitule' => 'Assurance de la pertinence des mécanismes ou outils de suivi et de contrôle du projet',
      'description' => '',
      'type' => 'formulaire',
      'elements' => [
      [
          'element_type' => 'field',
          'ordre_affichage' => 1,
          'label' => 'Méthodes de suivi et le contrôle',
          'info' => '',
          'key' => 'methodes_suivi_controle',
          'attribut' => 'methodes_suivi_controle',
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
          'label' => 'Commentaire sur les méthodes de suivi et le contrôle',
          'info' => '',
          'key' => 'commentaire_methodes_suivi_controle',
          'attribut' => 'commentaire_methodes_suivi_controle',
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
          'label' => 'Identification des écarts à combler',
          'info' => '',
          'key' => 'identification_écarts_combler',
          'attribut' => 'identification_écarts_combler',
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
    ],
  ],
];

    public function run(): void
    {
        DB::beginTransaction();

        try {
            $categorieDocument = CategorieDocument::updateOrCreate([
                'slug' => 'canevas-check-liste-de-suivi-etude-de-faisabilite-organisationnelle-juridique'
            ], [
                'nom' => "Canevas check liste de suivi etude de faisabilite organisationnelle juridique",
                'slug' => 'canevas-check-liste-de-suivi-etude-de-faisabilite-organisationnelle-juridique',
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
