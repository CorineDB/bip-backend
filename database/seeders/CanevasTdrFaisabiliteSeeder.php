<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Document;
use App\Models\CategorieDocument;

class CanevasTdrFaisabiliteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer ou récupérer la catégorie
        $categorie = CategorieDocument::firstOrCreate([
            'slug' => 'canevas-tdr-faisabilite'
        ], [
            'nom' => 'Canevas des TDRs de faisabilité',
            'description' => 'Catégorie pour les canevas de rédaction des termes de référence de faisabilité',
            "format" => "formulaire"
        ]);

        // Créer le document canevas
        $document = Document::updateOrCreate([
            'slug' => 'canevas-redaction-tdr-faisabilite'
        ], [
            'nom' => 'Canevas de rédaction des TDRs de faisabilité',
            'description' => 'Canevas de rédaction des termes de référence de faisabilité',
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
            foreach ($champs as $key => $champ) {
                $this->createChamp($champ, $document);
            }
        }

        $this->command->info('Canevas de rédaction des TDRs de faisabilité créé avec succès');
    }

    /**
     * Créer un champ avec validation des données
     */
    private function createChamp(array $champData, $document, $section = null): void
    {
        //dd(collect($champData)->pluck("label"));
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
