<?php

namespace Database\Seeders;

use App\Models\Secteur;
use App\Models\TypeProgramme;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgrammeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //$pag = TypeProgramme::where("slug", 'pag')->first()

        $pag = TypeProgramme::firstOrCreate([
            'slug' => 'pag'
        ], [
            'type_programme' => "Programme d'action du gouvernement"
        ]);

        $pilier_pag = ["Consolider la démocratie, l’état de droit et la bonne gouvernance", "Transformation structurelle de l’économie", "Améliorer les conditions de vie des populations"];

        $axe_pag = [
            "Renforcement de la démocratie et de l’État de droit",

            "Amélioration de la gouvernance",

            "Assainissement du cadre macro‑économique et maintien de sa stabilité",

            "Accélération de la croissance économique",

            "Promotion d'une éducation de qualité et de la formation technique et professionnelle (EFTP)",

            "Amélioration de l'accès aux services sociaux de base et à la protection sociale",

            "Développement territorial équilibré et durable"
        ];

        $action_pag = [
            "Construction, réhabilitation et équipement des écoles primaires, maternelles et secondaires dans plusieurs départements (Ouémé, Atlantique, Borgou, Collines…)",

            "Création de 30 lycées techniques agricoles modernes et 7 écoles de métiers",

            "Rénovation de 17 anciens lycées techniques, labs et bibliothèques dans l’enseignement secondaire général",

            "Mise en place d’un Centre d’excellence et d’un pôle universitaire international d’innovation",
            "Poursuite du Programme National d’Alimentation Scolaire Intégré (PNASI), visant à plus de 1 million d’élèves bénéficiaires avec cantines scolaires, appui à l’agriculture locale et santé-scolarité",
            "Déploiement de 45 projets phares et 95 projets sectoriels, accompagnés de 19 réformes institutionnelles alignées sur les priorités économiques (infrastructures, numérique, agriculture, énergie, mines…)",
            "Grands projets d’urbanisme et réaménagement (notamment préparer des espaces publics, reloger des populations, embellir Cotonou)",

            "Implantations dans le cadre de PPP pour les infrastructures urbaines, logistqiues et touristiques"
        ];


        # code...
        \App\Models\TypeProgramme::firstOrCreate([
            'slug' => Str::slug("pilier-pag"),
        ], [
            'type_programme' => "Piliers du PAG",
            "typeId" => $pag->id
        ]);

        $axe = \App\Models\TypeProgramme::firstOrCreate([
            'slug' => Str::slug("axe-pag"),
        ], [
            'type_programme' => "Axes du PAG",
            "typeId" => $pag->id
        ]);

        \App\Models\TypeProgramme::firstOrCreate([
            'slug' => Str::slug("action-pag"),
        ], [
            'type_programme' => "Actions du PAG",
            "typeId" => $axe->id
        ]);

        DB::table("composants_programme")->truncate();
        foreach ($pag->children as $key => $child) {
            if ($child->slug === 'pilier-pag') {
                foreach ($pilier_pag as $key => $pilier) {
                    # code...
                    \App\Models\ComposantProgramme::firstOrCreate([
                        'slug' => Str::slug($pilier),
                    ], [
                        'indice' => $key,
                        'intitule' => $pilier,
                        "slug" => Str::slug($pilier),
                        "typeId" => $child->id
                    ]);
                }
            }
            if ($child->slug === 'axe-pag') {
                foreach ($axe_pag as $key => $axe) {
                    # code...
                    \App\Models\ComposantProgramme::firstOrCreate([
                        'slug' => Str::slug($axe),
                    ], [
                        'indice' => $key,
                        'intitule' => $axe,
                        "slug" => Str::slug($axe),
                        "typeId" => $child->id
                    ]);
                }
            }
            if ($child->slug === 'action-pag') {
                foreach ($action_pag as $key => $action) {
                    # code...
                    \App\Models\ComposantProgramme::firstOrCreate([
                        'slug' => Str::slug($action),
                    ], [
                        'indice' => $key,
                        'intitule' => $action,
                        "slug" => Str::slug($action),
                        "typeId" => $child->id
                    ]);
                }
            }
        }


        //** PND */

        $pnd_comp = [
            "Faire du capital humain le levier de développement" => [
                "objectifs" => [
                    "Renforcer le capital humain et améliorer le bien-être" => [
                        "resultats" => [
                            "La population a un meilleur accès aux soins de santé" => [
                                "axes" => [
                                    "Santé et protection sociale" => [
                                        "actions" => [
                                            "Réhabiliter les centres de santé",
                                            "Renforcer le RAMU",
                                            "Recruter du personnel médical qualifié"
                                        ]
                                    ]
                                ]
                            ],
                            "Les jeunes sont mieux formés et insérés dans la vie active" => [
                                "axes" => [
                                    "Éducation et formation" => [
                                        "actions" => [
                                            "Moderniser les lycées techniques",
                                            "Mettre en œuvre la réforme de l’EFTP",
                                            "Financer les stages et formations qualifiantes"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            "Accélérer la transformation structurelle de l’économie" => [
                "objectifs" => [
                    "Dynamiser les secteurs porteurs de croissance" => [
                        "resultats" => [
                            "La productivité agricole est améliorée" => [
                                "axes" => [
                                    "Agriculture et agro-industrie" => [
                                        "actions" => [
                                            "Installer des périmètres irrigués",
                                            "Distribuer des intrants améliorés",
                                            "Encourager la transformation locale"
                                        ]
                                    ]
                                ]
                            ],
                            "L’économie numérique contribue davantage au PIB" => [
                                "axes" => [
                                    "TIC et innovation" => [
                                        "actions" => [
                                            "Déployer la fibre optique",
                                            "Créer des incubateurs numériques",
                                            "Digitaliser les services publics"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],

            "Assurer une gouvernance efficace et moderne" => [
                "objectifs" => [
                    "Renforcer la démocratie et l’État de droit" => [
                        "resultats" => [
                            "Les institutions judiciaires sont accessibles et efficaces" => [
                                "axes" => [
                                    "Justice et sécurité" => [
                                        "actions" => [
                                            "Construire des tribunaux de proximité",
                                            "Moderniser les procédures judiciaires"
                                        ]
                                    ]
                                ]
                            ],
                            "L’administration publique est plus performante et transparente" => [
                                "axes" => [
                                    "Gouvernance publique" => [
                                        "actions" => [
                                            "Digitaliser les procédures administratives",
                                            "Renforcer les capacités des agents de l’État"
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        //$pnd = TypeProgramme::where("slug", 'pnd')->first();
        $pnd = TypeProgramme::firstOrCreate([
            'slug' => 'pnd',
            'type_programme' => "Programme de Developpement durable"
        ], [
            'type_programme' => "Programme de Developpement durable",
            'slug' => 'pnd',
        ]);

        $orientationCount = 1;
        foreach ($pnd_comp as $key => $obj) {

            # code...
            $type = \App\Models\TypeProgramme::firstOrCreate([
                'slug' => Str::slug("orientation-strategique-pnd"),
            ], [
                'type_programme' => "Orientation stratégique du PND",
                "slug" => Str::slug("orientation-strategique-pnd"),
                "typeId" => $pnd->id
            ]);

            # code...
            \App\Models\ComposantProgramme::firstOrCreate([
                'slug' => Str::slug($key),
            ], [
                'indice' => $orientationCount,
                'intitule' => $key,
                "slug" => Str::slug($key),
                "typeId" => $type->id
            ]);

            # code...
            $objectif = \App\Models\TypeProgramme::firstOrCreate([
                'slug' => Str::slug("objectif-strategique-pnd"),
            ], [
                'type_programme' => "Objectif stratégique du PND",
                "slug" => Str::slug("objectif-strategique-pnd"),
                "typeId" => $type->id
            ]);

            $objectifCount = 1;
            foreach ($obj["objectifs"] as $objectif_str => $resultats_strategique) {

                # code...
                \App\Models\ComposantProgramme::firstOrCreate([
                    'slug' => Str::slug($objectif_str),
                ], [
                    'indice' => $objectifCount,
                    'intitule' => $objectif_str,
                    "slug" => Str::slug($objectif_str),
                    "typeId" => $objectif->id
                ]);


                # code...
                $resultats = \App\Models\TypeProgramme::firstOrCreate([
                    'slug' => Str::slug("resultats-strategique-pnd"),
                ], [
                    'type_programme' => "Resultats stratégique du PND",
                    "slug" => Str::slug("resultats-strategique-pnd"),
                    "typeId" => $objectif->id
                ]);

                $resultatCount = 1;
                $resultats_strategique = $resultats_strategique["resultats"];
                foreach ($resultats_strategique as $resultats_str => $axes_strategique) {

                    # code...
                    \App\Models\ComposantProgramme::firstOrCreate([
                        'slug' => Str::slug($resultats_str),
                    ], [
                        'indice' => $resultatCount,
                        'intitule' => $resultats_str,
                        "slug" => Str::slug($resultats_str),
                        "typeId" => $resultats->id
                    ]);

                    # code...
                    $axe = \App\Models\TypeProgramme::firstOrCreate([
                        'slug' => Str::slug("axe-strategique-pnd"),
                    ], [
                        'type_programme' => "Axes stratégique du PND",
                        "typeId" => $objectif->id
                    ]);

                    $axeCount = 1;
                    $axes_strategique = $axes_strategique["axes"];
                    foreach ($axes_strategique as $axe_str => $actions_strategique) {

                        # code...
                        \App\Models\ComposantProgramme::firstOrCreate([
                            'slug' => Str::slug($axe_str),
                        ], [
                            'indice' => $axeCount,
                            'intitule' => $axe_str,
                            "slug" => Str::slug($axe_str),
                            "typeId" => $axe->id
                        ]);

                        $axeCount++;
                    }
                    $resultatCount++;
                }
                $objectifCount++;
            }
        }

        $grands_secteur = [
            'Infrastructures et cadre de vie' => [
                'Transport' => [
                    'Routes',
                    'Ponts',
                    'Voies urbaines',
                    'Corridors logistiques'
                ],
                'Urbanisme et habitat' => [
                    'Assainissement',
                    'Logements sociaux',
                    'Embellissement des villes'
                ],
                'Eau' => [
                    'Accès à l’eau potable',
                    'Hydraulique urbaine',
                    'Hydraulique villageoise'
                ],
                'Énergie' => [
                    'Extension réseau électrique',
                    'Énergie solaire',
                    'Centrales thermiques'
                ]
            ],
            'Éducation et formation' => [
                'Éducation de base' => [
                    'Maternelle',
                    'Primaire',
                    'Cantines scolaires (PNASI)'
                ],
                'Éducation secondaire' => [
                    'Collèges',
                    'Lycées techniques',
                    'Internats rénovés'
                ],
                'Formation professionnelle' => [
                    'Lycées techniques agricoles',
                    'Écoles des métiers',
                    'EFTP'
                ],
                'Enseignement supérieur' => [
                    'Pôle universitaire d’Abomey-Calavi',
                    'Centre d’excellence'
                ]
            ],
            'Santé et protection sociale' => [
                'Santé' => [
                    'Hôpitaux',
                    'CHU',
                    'Centres de santé',
                    'Équipements médicaux'
                ],
                'Protection sociale' => [
                    'RAMU',
                    'Assurance santé universelle',
                    'Filets sociaux',
                    'Prise en charge scolaire'
                ]
            ],
            'Agriculture, élevage et pêche' => [
                'Agriculture' => [
                    'Zones agricoles à fort potentiel',
                    'Mécanisation',
                    'Irrigation'
                ],
                'Élevage' => [
                    'Santé animale',
                    'Transformation des produits'
                ],
                'Pêche' => [
                    'Halieutique',
                    'Aquaculture',
                    'Valorisation des pêcheries'
                ]
            ],
            'Numérique, TIC et innovation' => [
                'Numérique' => [
                    'Fibre optique',
                    'Couverture 4G',
                    'Dématérialisation des services'
                ],
                'Innovation' => [
                    'E-services',
                    'E-éducation',
                    'Identité numérique'
                ]
            ],
            'Gouvernance, justice et institutions' => [
                'Justice' => [
                    'Infrastructures judiciaires',
                    'Modernisation des procédures'
                ],
                'Sécurité' => [
                    'Police républicaine',
                    'Équipements',
                    'Casernes'
                ],
                'Gouvernance' => [
                    'Lutte contre la corruption',
                    'Réformes fiscales',
                    'Administration modernisée'
                ]
            ],
            'Économie, emploi, industrie et artisanat' => [
                'Industrie' => [
                    'Zones économiques spéciales (Glo-Djigbé)',
                    'Transformation locale'
                ],
                'Artisanat' => [
                    'Structuration des artisans',
                    'Villages artisanaux'
                ],
                'Emploi' => [
                    'Plan emploi jeunes',
                    'Auto-emploi',
                    'Stages',
                    'ANPE'
                ]
            ],
            'Tourisme, culture et patrimoine' => [
                'Tourisme' => [
                    'Routes touristiques',
                    'Hôtels',
                    'Monuments',
                    'Circuits patrimoniaux'
                ],
                'Culture' => [
                    'Musées',
                    'Patrimoine historique',
                    'Sites religieux'
                ]
            ],
            'Environnement et développement durable' => [
                'Environnement' => [
                    'Reboisement',
                    'Protection côtière',
                    'Gestion des déchets'
                ],
                'Changement climatique' => [
                    'Résilience',
                    'Biodiversité',
                    'Énergies renouvelables'
                ]
            ]
        ];


        // Insertion dans la table `secteurs`
        foreach ($grands_secteur as $grandNom => $secteurs) {
            $grand = Secteur::firstOrCreate([
                'slug' => Str::slug($grandNom)
            ], [
                'nom' => $grandNom,
                'slug' => Str::slug($grandNom),
                'type' => 'grand-secteur',
                'secteurId' => null,
            ]);

            foreach ($secteurs as $secteurNom => $sousSecteurs) {
                $secteur = Secteur::firstOrCreate([
                    'slug' => Str::slug($secteurNom)
                ], [
                    'nom' => $secteurNom,
                    'slug' => Str::slug($secteurNom),
                    'type' => 'secteur',
                    'secteurId' => $grand->id,
                ]);

                foreach ($sousSecteurs as $sousNom) {
                    Secteur::firstOrCreate([
                        'slug' => Str::slug($sousNom)
                    ], [
                        'nom' => $sousNom,
                        'slug' => Str::slug($sousNom),
                        'type' => 'sous-secteur',
                        'secteurId' => $secteur->id,
                    ]);
                }
            }
        }
    }
}
