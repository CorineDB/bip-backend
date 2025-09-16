<?php

namespace Database\Seeders;

use App\Helpers\SlugHelper;
use App\Models\ComposantProgramme;
use App\Models\Secteur;
use App\Models\TypeIntervention;
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

        $pag_structure = [
            "Renforcer la démocratie, l’État de droit et la bonne gouvernance" => [
                "axes" => [
                    "Renforcement de la démocratie et de l’État de droit" => [
                        "actions" => [
                            "Consolider la démocratie, l’État de droit et les libertés",
                            "Promouvoir une justice moderne, indépendante, efficace et accessible à tous",
                            "Renforcer la liberté de presse et l’accès public à l’information"
                        ]
                    ],
                    "Consolidation de la bonne gouvernance" => [
                        "actions" => [
                            "Accélérer la modernisation de l’administration publique",
                            "Améliorer la gouvernance locale et l’intercommunalité",
                            "Poursuivre le renforcement de la protection des droits des personnes",
                            "Renforcer la diplomatie de développement"
                        ]
                    ],
                    "Renforcement du cadre macro-économique et maintien de sa stabilité" => [
                        "actions" => [
                            "Renforcer le cadre macro-économique et l’intégration régionale",
                            "Poursuivre l’assainissement de la gestion des finances publiques",
                            "Poursuivre l’amélioration du climat des affaires"
                        ]
                    ],
                ]
            ],

            "Poursuivre la transformation structurelle de l’économie" => [
                "axes" => [
                    "Accélération de la croissance économique" => [
                        "actions" => [
                            "Intensifier la construction des infrastructures modernes",
                            "Réaliser l’autonomie énergétique",
                            "Accroître les investissements dans les infrastructures et services numériques",
                            "Consolider les performances du secteur agricole",
                            "Faire du Bénin une destination touristique majeure",
                            "Stimuler l’industrialisation",
                            "Promouvoir la création d’emplois pour les jeunes et les femmes"
                        ]
                    ],
                    "Promotion d’une éducation de qualité et de l’EFTP" => [
                        "actions" => [
                            "Poursuivre la restructuration du système éducatif",
                            "Développer l’enseignement et la formation technique et professionnelle",
                            "Promouvoir la recherche et l’innovation",
                            "Promouvoir l’alphabétisation et l’éducation des adultes"
                        ]
                    ],
                ]
            ],

            "Poursuivre l’amélioration du bien-être social des populations" => [
                "axes" => [
                    "Amélioration de l’accès des populations aux services sociaux de base et à la protection sociale" => [
                        "actions" => [
                            "Généraliser le programme d’assurance pour le renforcement du capital humain",
                            "Renforcer le système de santé et assurer une couverture sanitaire efficace",
                            "Garantir l’accès universel à l’eau potable",
                            "Renforcer les infrastructures sportives et la professionnalisation des acteurs"
                        ]
                    ],
                    "Renforcement du développement équilibré et durable de l’espace national" => [
                        "actions" => [
                            "Poursuivre l’amélioration du cadre de vie et l’aménagement équilibré du territoire",
                            "Renforcer la préservation de l’environnement et la résilience aux changements climatiques",
                            "Promouvoir le développement local et l’accès des communes aux ressources de qualité"
                        ]
                    ]
                ]
            ]
        ];

        DB::table("types_programme")->truncate();
        $pag = TypeProgramme::updateOrCreate([
            'slug' => 'pag'
        ], [
            'type_programme' => "Programme d'Action du Gouvernement."
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
        $pilier = \App\Models\TypeProgramme::updateOrCreate([
            'slug' => SlugHelper::generate(Str::slug("pilier-pag")),
        ], [
            'type_programme' => "Pilier du programme d'action du gouvernement",
            "typeId" => $pag->id
        ]);

        $axe = \App\Models\TypeProgramme::updateOrCreate([
            'slug' => SlugHelper::generate(Str::slug("axe-pag")),
        ], [
            'type_programme' => "Axe du programme d'action du gouvernement",
            "typeId" => $pilier->id
        ]);

        $action = \App\Models\TypeProgramme::updateOrCreate([
            'slug' => SlugHelper::generate(Str::slug("action-pag")),
        ], [
            'type_programme' => "Action du programme d'action du gouvernement",
            "typeId" => $axe->id
        ]);

        DB::table("composants_programme")->truncate();
        /* foreach ($pag->children as $key => $child) {
            if ($child->slug === 'pilier-pag') {
                foreach ($pilier_pag as $key => $pilier) {
                    # code...
                    \App\Models\ComposantProgramme::updateOrCreate([
                        'slug' => SlugHelper::generate(Str::slug($pilier)),
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
                    \App\Models\ComposantProgramme::updateOrCreate([
                        'slug' => SlugHelper::generate(Str::slug($axe)),
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
                    \App\Models\ComposantProgramme::updateOrCreate([
                        'slug' => SlugHelper::generate(Str::slug($action)),
                    ], [
                        'indice' => $key,
                        'intitule' => $action,
                        "slug" => Str::slug($action),
                        "typeId" => $child->id
                    ]);
                }
            }
        } */

        $pilierCount = 1;
        foreach ($pag_structure as $pilierData => $axes) {
            /**
             * create([
             *    'libelle' => $pilier,
             *     'type'    => 'pilier',
             *     'parent_id' => null
             * ]);
             */
            // Création du pilier
            $pilierModel = ComposantProgramme::updateOrCreate([
                'slug' => SlugHelper::generate(Str::slug($pilierData)),
            ], [
                'indice' => $pilierCount,
                'intitule' => $pilierData,
                "slug" => Str::slug($pilierData),
                'parentId' => null,
                "typeId" => $pilier->id
            ]);

            $axeCount = 1;
            foreach ($axes['axes'] as $axeData => $details) {
                // Création de l’axe rattaché au pilier
                $axeModel = ComposantProgramme::updateOrCreate([
                    'slug' => SlugHelper::generate(Str::slug($axeData)),
                ], [
                    'indice' => $axeCount,
                    'intitule' => $axeData,
                    "slug" => Str::slug($axeData),
                    'parentId' => $pilierModel->id,
                    "typeId" => $axe->id
                ]);

                /*create([
                    'libelle' => $axe,
                    'type'    => 'axe',
                    'parent_id' => $pilierModel->id
                ]);*/

                $actionCount = 1;
                foreach ($details['actions'] as $actionData) {
                    // Création des actions rattachées à l’axe
                    ComposantProgramme::updateOrCreate([
                        'slug' => SlugHelper::generate(Str::slug($actionData)),
                    ], [
                        'indice' => $actionCount,
                        'intitule' => $actionData,
                        "slug" => SlugHelper::generate(Str::slug($actionData)),
                        'parentId' => $axeModel->id,
                        "typeId" => $action->id
                    ]);
                    /*create([
                        'libelle' => $action,
                        'type'    => 'action',
                        'parent_id' => $axeModel->id
                    ]);
                    */

                    $actionCount++;
                }

                $axeCount++;
            }

            $pilierCount++;
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
        $pnd = TypeProgramme::updateOrCreate([
            'slug' => 'pnd',
        ], [
            'type_programme' => "Programme de Developpement Durable.",
            'slug' => 'pnd',
        ]);

        $orientationCount = 1;
        foreach ($pnd_comp as $key => $obj) {

            # code...
            $type = \App\Models\TypeProgramme::updateOrCreate([
                'slug' => SlugHelper::generate(Str::slug("orientation-strategique-pnd")),
            ], [
                'type_programme' => "Orientation stratégique du PND",
                "slug" => Str::slug("orientation-strategique-pnd"),
                "typeId" => $pnd->id
            ]);

            # code...
            \App\Models\ComposantProgramme::updateOrCreate([
                'slug' => SlugHelper::generate(Str::slug($key)),
            ], [
                'indice' => $orientationCount,
                'intitule' => $key,
                "slug" => SlugHelper::generate(Str::slug($key)),
                "typeId" => $type->id
            ]);

            # code...
            $objectif = \App\Models\TypeProgramme::updateOrCreate([
                'slug' => SlugHelper::generate(Str::slug("objectif-strategique-pnd")),
            ], [
                'type_programme' => "Objectif stratégique du PND",
                "slug" => Str::slug("objectif-strategique-pnd"),
                "typeId" => $type->id
            ]);

            $objectifCount = 1;
            foreach ($obj["objectifs"] as $objectif_str => $resultats_strategique) {

                # code...
                \App\Models\ComposantProgramme::updateOrCreate([
                    'slug' => SlugHelper::generate(Str::slug($objectif_str)),
                ], [
                    'indice' => $objectifCount,
                    'intitule' => $objectif_str,
                    "slug" => Str::slug($objectif_str),
                    "typeId" => $objectif->id
                ]);


                # code...
                $resultats = \App\Models\TypeProgramme::updateOrCreate([
                    'slug' => SlugHelper::generate(Str::slug("resultats-strategique-pnd")),
                ], [
                    'type_programme' => "Resultats stratégique du PND",
                    "slug" => Str::slug("resultats-strategique-pnd"),
                    "typeId" => $objectif->id
                ]);

                $resultatCount = 1;
                $resultats_strategique = $resultats_strategique["resultats"];
                foreach ($resultats_strategique as $resultats_str => $axes_strategique) {

                    # code...
                    \App\Models\ComposantProgramme::updateOrCreate([
                        'slug' => SlugHelper::generate(Str::slug($resultats_str)),
                    ], [
                        'indice' => $resultatCount,
                        'intitule' => $resultats_str,
                        "slug" => Str::slug($resultats_str),
                        "typeId" => $resultats->id
                    ]);

                    # code...
                    $axe = \App\Models\TypeProgramme::updateOrCreate([
                        'slug' => SlugHelper::generate(Str::slug("axe-strategique-pnd")),
                    ], [
                        'type_programme' => "Axes stratégique du PND",
                        "typeId" => $objectif->id
                    ]);

                    $axeCount = 1;
                    $axes_strategique = $axes_strategique["axes"];
                    foreach ($axes_strategique as $axe_str => $actions_strategique) {

                        # code...
                        \App\Models\ComposantProgramme::updateOrCreate([
                            'slug' => SlugHelper::generate(Str::slug($axe_str)),
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
                    'Routes' => [
                        'Réhabilitation',
                        'Construction',
                        'Entretien',
                        'Signalisation routière'
                    ],
                    'Ponts' => [
                        'Inspection',
                        'Construction',
                        'Réparation',
                        'Entretien'
                    ],
                    'Voies urbaines' => [
                        'Aménagement',
                        'Éclairage public',
                        'Mobilier urbain',
                        'Gestion du trafic'
                    ],
                    'Corridors logistiques' => [
                        'Optimisation',
                        'Sécurité',
                        'Développement d’infrastructures',
                        'Gestion des flux'
                    ]
                ],
                'Urbanisme et habitat' => [

                    'Assainissement' => [
                        'Réseaux d’égouts',
                        'Gestion des eaux pluviales',
                        'Stations de traitement',
                        'Évacuation des déchets'
                    ],
                    'Logements sociaux' => [
                        'Construction',
                        'Réhabilitation',
                        'Gestion locative',
                        'Financement'
                    ],
                    'Embellissement des villes' => [
                        'Espaces verts',
                        'Mobilier urbain',
                        'Peinture et rénovation',
                        'Aménagement paysager'
                    ]
                ],
                'Eau' => [
                    'Accès à l’eau potable' => [
                        'Forages',
                        'Distribution d’eau',
                        'Traitement de l’eau',
                        'Maintenance des infrastructures'
                    ],
                    'Hydraulique urbaine' => [
                        'Gestion réseau',
                        'Stockage',
                        'Pompage',
                        'Traitement des eaux usées'
                    ],
                    'Hydraulique villageoise' => [
                        'Forages villageois',
                        'Petites adductions',
                        'Maintenance locale',
                        'Sensibilisation'
                    ],
                ],
                'Énergie' => [
                    'Extension réseau électrique' => [
                        'Pose de lignes',
                        'Postes de transformation',
                        'Maintenance',
                        'Développement rural'
                    ],
                    'Énergie solaire' => [
                        'Installation panneaux',
                        'Maintenance',
                        'Formation technique',
                        'Promotion'
                    ],
                    'Centrales thermiques' => [
                        'Construction',
                        'Exploitation',
                        'Maintenance',
                        'Sécurité'
                    ],
                ]
            ],
            'Éducation et formation' => [
                'Éducation de base' => [
                    'Maternelle' => [
                        'Construction et rénovation des salles de classe',
                        'Formation des enseignants',
                        'Fourniture de matériel pédagogique',
                        'Sensibilisation des parents'
                    ],
                    'Primaire' => [
                        'Développement des programmes scolaires',
                        'Formation continue des enseignants',
                        'Construction d’infrastructures sanitaires',
                        'Organisation d’activités extrascolaires'
                    ],
                    'Cantines scolaires (PNASI)' => [
                        'Mise en place des cantines',
                        'Gestion et supervision alimentaire',
                        'Sensibilisation à la nutrition',
                        'Suivi sanitaire des enfants'
                    ],
                ],
                'Éducation secondaire' => [
                    'Collèges' => [
                        'Réhabilitation des bâtiments scolaires',
                        'Développement des laboratoires',
                        'Formation des professeurs',
                        'Promotion des activités sportives'
                    ],
                    'Lycées techniques' => [
                        'Acquisition d’équipements spécialisés',
                        'Partenariats avec entreprises',
                        'Formations techniques avancées',
                        'Stages pratiques pour élèves'
                    ],
                    'Internats rénovés' => [
                        'Rénovation des dortoirs',
                        'Amélioration des infrastructures sanitaires',
                        'Gestion administrative',
                        'Programme de vie scolaire'
                    ],
                ],
                'Formation professionnelle' => [
                    'Lycées techniques agricoles' => [
                        'Modernisation des équipements',
                        'Développement de modules de formation',
                        'Partenariat avec exploitations agricoles',
                        'Organisation de stages'
                    ],
                    'Écoles des métiers' => [
                        'Acquisition d’outils professionnels',
                        'Formation continue des formateurs',
                        'Développement des cursus adaptés',
                        'Insertion professionnelle'
                    ],
                    'EFTP' => [ // Enseignement et formation technique et professionnelle
                        'Mise en place de centres de formation',
                        'Formation des formateurs',
                        'Développement des programmes pratiques',
                        'Partenariats industriels'
                    ],
                ],
                'Enseignement supérieur' => [
                    'Pôle universitaire d’Abomey-Calavi' => [
                        'Développement de la recherche',
                        'Construction d’amphithéâtres',
                        'Programmes d’échanges internationaux',
                        'Gestion administrative et académique'
                    ],
                    'Centre d’excellence' => [
                        'Renforcement des capacités',
                        'Bourses d’études',
                        'Innovation pédagogique',
                        'Ateliers de perfectionnement'
                    ],
                ]
            ],
            'Santé et protection sociale' => [
                'Santé' => [
                    'Hôpitaux' => [
                        'Construction et rénovation des infrastructures hospitalières',
                        'Acquisition de matériel médical',
                        'Formation du personnel soignant',
                        'Mise en place de services d’urgence'
                    ],
                    'CHU' => [ // Centres Hospitaliers Universitaires
                        'Développement de la recherche médicale',
                        'Formation spécialisée des médecins',
                        'Partenariats avec institutions internationales',
                        'Renforcement des services cliniques'
                    ],
                    'Centres de santé' => [
                        'Création de centres de proximité',
                        'Campagnes de vaccination',
                        'Sensibilisation à la santé publique',
                        'Suivi des patients chroniques'
                    ],
                    'Équipements médicaux' => [
                        'Acquisition et maintenance des équipements',
                        'Formation à l’utilisation des technologies',
                        'Déploiement d’équipements mobiles',
                        'Gestion des stocks et approvisionnements'
                    ],
                ],
                'Protection sociale' => [
                    'RAMU' => [ // Régime d'Assurance Maladie Universelle
                        'Mise en place du système d’assurance maladie',
                        'Sensibilisation des populations',
                        'Gestion des cotisations',
                        'Suivi et remboursement des prestations'
                    ],
                    'Assurance santé universelle' => [
                        'Élaboration des politiques d’assurance',
                        'Coordination avec les structures de santé',
                        'Éducation à l’assurance maladie',
                        'Suivi de la couverture sociale'
                    ],
                    'Filets sociaux' => [
                        'Identification des bénéficiaires',
                        'Distribution d’aides financières',
                        'Suivi des programmes sociaux',
                        'Évaluation d’impact'
                    ],
                    'Prise en charge scolaire' => [
                        'Soutien aux enfants vulnérables',
                        'Programmes nutritionnels',
                        'Assistance médicale scolaire',
                        'Sensibilisation des familles'
                    ],
                ],
            ],
            'Agriculture, élevage et pêche' => [
                'Agriculture' => [
                    'Zones agricoles à fort potentiel' => [
                        'Développement des infrastructures agricoles',
                        'Amélioration des sols',
                        'Promotion des cultures à haut rendement',
                        'Accès aux intrants agricoles'
                    ],
                    'Mécanisation' => [
                        'Acquisition de matériel agricole',
                        'Formation à l’utilisation des machines',
                        'Maintenance et réparation',
                        'Promotion de l’agriculture mécanisée'
                    ],
                    'Irrigation' => [
                        'Installation de systèmes d’irrigation',
                        'Gestion de l’eau pour l’agriculture',
                        'Formation à la gestion des ressources hydriques',
                        'Développement de cultures irriguées'
                    ],
                ],
                'Élevage' => [
                    'Santé animale' => [
                        'Vaccination et soins vétérinaires',
                        'Lutte contre les épizooties',
                        'Formation des éleveurs',
                        'Contrôle sanitaire des élevages'
                    ],
                    'Transformation des produits' => [
                        'Développement des unités de transformation',
                        'Formation aux bonnes pratiques',
                        'Mise en marché des produits transformés',
                        'Contrôle qualité'
                    ],
                ],
                'Pêche' => [
                    'Halieutique' => [
                        'Gestion durable des pêcheries',
                        'Réglementation de la pêche',
                        'Suivi des ressources halieutiques',
                        'Protection des zones de reproduction'
                    ],
                    'Aquaculture' => [
                        'Développement des fermes aquacoles',
                        'Formation des pisciculteurs',
                        'Gestion des systèmes aquacoles',
                        'Amélioration des races'
                    ],
                    'Valorisation des pêcheries' => [
                        'Transformation des produits de la pêche',
                        'Promotion des produits locaux',
                        'Développement des marchés',
                        'Contrôle qualité'
                    ],
                ]
            ],
            'Numérique, TIC et innovation' => [
                'Numérique' => [
                    'Fibre optique' => [
                        'Déploiement de réseaux fibre optique',
                        'Maintenance des infrastructures',
                        'Extension des réseaux aux zones rurales',
                        'Sécurisation des infrastructures'
                    ],
                    'Couverture 4G' => [
                        'Installation de stations de base 4G',
                        'Optimisation de la couverture réseau',
                        'Promotion de l’accès mobile internet',
                        'Formation à l’utilisation des technologies mobiles'
                    ],
                    'Dématérialisation des services' => [
                        'Développement de plateformes numériques',
                        'Digitalisation des procédures administratives',
                        'Formation des agents publics',
                        'Sensibilisation des usagers'
                    ],
                ],
                'Innovation' => [
                    'E-services' => [
                        'Création de services en ligne',
                        'Intégration de solutions mobiles',
                        'Sécurisation des données utilisateurs',
                        'Suivi et évaluation des services'
                    ],
                    'E-éducation' => [
                        'Développement de contenus éducatifs numériques',
                        'Mise en place de plateformes d’apprentissage',
                        'Formation des enseignants',
                        'Promotion de l’accès aux ressources numériques'
                    ],
                    'Identité numérique' => [
                        'Gestion des identités électroniques',
                        'Mise en place de systèmes d’authentification',
                        'Protection des données personnelles',
                        'Sensibilisation à la cybersécurité'
                    ],
                ]
            ],
            'Gouvernance, justice et institutions' => [
                'Justice' => [
                    'Infrastructures judiciaires' => [
                        'Construction de tribunaux',
                        'Modernisation des palais de justice',
                        'Amélioration des infrastructures pénitentiaires',
                        'Accessibilité aux services judiciaires'
                    ],
                    'Modernisation des procédures' => [
                        'Digitalisation des dossiers judiciaires',
                        'Réforme des processus juridiques',
                        'Formation des agents judiciaires',
                        'Mise en place de systèmes de gestion électronique'
                    ],
                ],
                'Sécurité' => [
                    'Police républicaine' => [
                        'Renforcement des effectifs',
                        'Formation continue',
                        'Équipements opérationnels',
                        'Déploiement sur le terrain'
                    ],
                    'Équipements' => [
                        'Acquisition de matériels',
                        'Maintenance des équipements',
                        'Modernisation technologique',
                        'Logistique sécuritaire'
                    ],
                    'Casernes' => [
                        'Construction de casernes',
                        'Rénovation des bâtiments',
                        'Sécurisation des locaux',
                        'Amélioration des conditions de travail'
                    ],
                ],
                'Gouvernance' => [
                    'Lutte contre la corruption' => [
                        'Campagnes de sensibilisation',
                        'Mise en place d’instances de contrôle',
                        'Renforcement des mécanismes de transparence',
                        'Formation anti-corruption'
                    ],
                    'Réformes fiscales' => [
                        'Simplification des procédures fiscales',
                        'Modernisation des administrations fiscales',
                        'Amélioration de la collecte des impôts',
                        'Soutien aux contribuables'
                    ],
                    'Administration modernisée' => [
                        'Digitalisation des services publics',
                        'Formation des fonctionnaires',
                        'Réorganisation des structures administratives',
                        'Amélioration de la qualité des services'
                    ],
                ]
            ],
            'Économie, emploi, industrie et artisanat' => [
                'Industrie' => [
                    'Zones économiques spéciales (Glo-Djigbé)' => [
                        'Aménagement et développement des zones',
                        'Incitations fiscales pour les entreprises',
                        'Développement des infrastructures logistiques',
                        'Promotion des investissements étrangers'
                    ],
                    'Transformation locale' => [
                        'Modernisation des unités de transformation',
                        'Soutien aux PME industrielles',
                        'Formation technique et professionnelle',
                        'Promotion des produits locaux'
                    ],
                ],
                'Artisanat' => [
                    'Structuration des artisans' => [
                        'Organisation en coopératives',
                        'Formation et certification',
                        'Accès aux marchés',
                        'Accompagnement technique et financier'
                    ],
                    'Villages artisanaux' => [
                        'Création et aménagement de villages',
                        'Promotion culturelle et touristique',
                        'Développement des infrastructures',
                        'Soutien à la commercialisation'
                    ],
                ],
                'Emploi' => [
                    'Plan emploi jeunes' => [
                        'Programmes de formation',
                        'Aides à l’embauche',
                        'Insertion professionnelle',
                        'Suivi et accompagnement'
                    ],
                    'Auto-emploi' => [
                        'Microcrédits',
                        'Formation entrepreneuriale',
                        'Soutien à la création d’entreprise',
                        'Accompagnement post-création'
                    ],
                    'Stages' => [
                        'Partenariats avec entreprises',
                        'Développement de compétences',
                        'Insertion dans le marché du travail',
                        'Suivi des stagiaires'
                    ],
                    'ANPE' => [
                        'Services d’orientation professionnelle',
                        'Aide à la recherche d’emploi',
                        'Organisation de foires de l’emploi',
                        'Formation continue'
                    ]
                ]
            ],
            'Tourisme, culture et patrimoine' => [
                'Tourisme' => [
                    'Routes touristiques' => [
                        'Aménagement et signalisation',
                        'Développement d’aires de repos',
                        'Promotion des itinéraires',
                        'Sécurité routière touristique'
                    ],
                    'Hôtels' => [
                        'Construction et rénovation',
                        'Certification qualité',
                        'Formation du personnel',
                        'Promotion du tourisme durable'
                    ],
                    'Monuments' => [
                        'Restauration et conservation',
                        'Mise en valeur historique',
                        'Accessibilité au public',
                        'Sensibilisation culturelle'
                    ],
                    'Circuits patrimoniaux' => [
                        'Création de parcours thématiques',
                        'Intégration communautaire',
                        'Promotion auprès des touristes',
                        'Développement d’activités connexes'
                    ],
                ],
                'Culture' => [
                    'Musées' => [
                        'Conservation des collections',
                        'Modernisation des expositions',
                        'Animations culturelles',
                        'Formation des guides'
                    ],
                    'Patrimoine historique' => [
                        'Inventaire des sites',
                        'Protection juridique',
                        'Restauration',
                        'Valorisation touristique'
                    ],
                    'Sites religieux' => [
                        'Entretien et restauration',
                        'Organisation des pèlerinages',
                        'Promotion du patrimoine immatériel',
                        'Développement des infrastructures'
                    ],
                ]
            ],
            'Environnement et développement durable' => [
                'Environnement' => [
                    'Reboisement' => [
                        'Plantation d’arbres',
                        'Protection des forêts',
                        'Sensibilisation communautaire',
                        'Suivi de la croissance'
                    ],
                    'Protection côtière' => [
                        'Construction de digues',
                        'Préservation des mangroves',
                        'Gestion de l’érosion',
                        'Surveillance des zones sensibles'
                    ],
                    'Gestion des déchets' => [
                        'Collecte et tri sélectif',
                        'Recyclage et valorisation',
                        'Traitement des déchets dangereux',
                        'Campagnes de sensibilisation'
                    ],
                ],
                'Changement climatique' => [
                    'Résilience' => [
                        'Planification des risques',
                        'Formation aux mesures d’adaptation',
                        'Soutien aux communautés vulnérables',
                        'Développement d’infrastructures résistantes'
                    ],
                    'Biodiversité' => [
                        'Conservation des espèces',
                        'Protection des habitats naturels',
                        'Recherche et suivi',
                        'Programmes d’éducation environnementale'
                    ],
                    'Énergies renouvelables' => [
                        'Installation de panneaux solaires',
                        'Développement de parcs éoliens',
                        'Promotion de la biomasse',
                        'Optimisation de l’efficacité énergétique'
                    ]
                ]
            ]
        ];


        // Insertion dans la table `secteurs`
        foreach ($grands_secteur as $grandNom => $secteurs) {
            $grand = Secteur::updateOrCreate([
                'slug' => SlugHelper::generate(Str::slug($grandNom))
            ], [
                'nom' => $grandNom,
                'slug' => SlugHelper::generate(Str::slug($grandNom)),
                'type' => 'grand-secteur',
                'secteurId' => null,
            ]);

            foreach ($secteurs as $secteurNom => $sousSecteurs) {
                $secteur = Secteur::updateOrCreate([
                    'slug' => SlugHelper::generate(Str::slug($secteurNom))
                ], [
                    'nom' => $secteurNom,
                    'slug' => SlugHelper::generate(Str::slug($secteurNom)),
                    'type' => 'secteur',
                    'secteurId' => $grand->id,
                ]);

                foreach ($sousSecteurs as $sousNom => $typesIntervention) {
                    $secteur = Secteur::updateOrCreate([
                        'slug' => SlugHelper::generate(Str::slug($sousNom))
                    ], [
                        'nom' => $sousNom,
                        'slug' => SlugHelper::generate(Str::slug($sousNom)),
                        'type' => 'sous-secteur',
                        'secteurId' => $secteur->id,
                    ]);

                    // Ici, $typesIntervention est la liste des types (niveau 3)
                    foreach ($typesIntervention as $typeNom) {
                        // Créer type d'intervention lié au secteur (niveau 2)
                        TypeIntervention::updateOrCreate([
                            'type_intervention' => $typeNom,
                            'secteurId' => $secteur->id,
                        ], [
                            'type_intervention' => $typeNom,
                            'secteurId' => $secteur->id,
                        ]);
                    }
                }
            }
        }
    }
}
