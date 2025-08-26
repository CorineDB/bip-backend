<?php

namespace Database\Seeders;

use App\Helpers\SlugHelper;
use App\Models\Secteur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistMesuresAdaptationSeeder extends Seeder
{
    protected $criteresEtNotationsParSecteurs = [];

    protected $secteurs;

    protected $payload = [
        "type" => "Checklist des mesures d'adaptation - CONTRÔLE DES ADAPTATIONS POUR LES PROJETS À HAUT RISQUE",
        "slug" => "checklist-mesures-adaptation-haut-risque",
        "is_mandatory" => true,
        "description" => "Checklist des mesures d'adaptation pour les projets à haut risque climatique",
        "criteres" => [
            [
                "intitule" => "Chaleur",
                "description" => "",
                "ponderation" => 25,
                "is_mandatory" => true,
                "secteurs" => [
                    [
                        "secteur_id" => 47, // ceci doit etre recuperer dynamiquement si n'existe creer le secteur agriculture
                        "mesures" => [
                            [
                                "libelle" => "Gestion des ressources en eau",
                                "valeur" => "gestion-des-ressources-en-eau",
                                "description" => ""
                            ],
                            [
                                "libelle" => "Systèmes de refroidissement efficaces",
                                "valeur" => "gestion-des-ressources-en-eau",
                                "description" => ""
                            ],
                            [
                                "libelle" => "Comptage et tarifs de l'eau",
                                "valeur" => "gestion-des-ressources-en-eau",
                                "description" => ""
                            ],
                        ]
                    ],
                    [
                        "secteur_id" => 15, // ceci doit etre recuperer dynamiquement si n'existe creer le secteur Energie
                        "mesures" => [
                            [
                                "libelle" => "Irrigation à haut rendement",
                                "valeur" => "irrigation-a-haut-rendement",
                                "description" => ""
                            ],
                            [
                                "libelle" => "Composants éoliens/solaires/TIC résistants à la chaleur",
                                "valeur" => "irrigation-a-haut-rendement",
                                "description" => ""
                            ],
                            [
                                "libelle" => "Gestion de la demande / Réduction de l'ENC",
                                "valeur" => "irrigation-a-haut-rendement",
                                "description" => ""
                            ],
                        ]
                    ],
                    [
                        "secteur_id" => 11, // ceci doit etre recuperer dynamiquement si n'existe creer le secteur eau
                        "mesures" => [
                            [
                                "libelle" => "Exploitation et maintenance des exploitations agricoles",
                                "valeur" => "exploitation-et-maintenance-des-exploitations-agricoles",
                                "description" => ""
                            ],
                            [
                                "libelle" => "Choisissez des emplacements plus frais",
                                "valeur" => "exploitation-et-maintenance-des-exploitations-agricoles",
                                "description" => ""
                            ],
                            [
                                "libelle" => "Réutilisation et dessalement de l'eau",
                                "valeur" => "exploitation-et-maintenance-des-exploitations-agricoles",
                                "description" => ""
                            ],
                        ]
                    ],
                ]
            ],
            [
                "intitule" => "Sécheresse",
                "description" => null,
                "ponderation" => 25,
                "ponderation_pct" => "25%",
                "is_mandatory" => true,
                "secteurs" => [
                    [
                        "secteur_id" => 47, // ceci doit etre recuperer dynamiquement si n'existe creer le secteur agriculture
                        "mesures" => [
                            [
                                "libelle" => "Spécifications matérielles",
                                "valeur" => "specifications-materielles",
                                "description" => null
                            ],
                            [
                                "libelle" => "Normes de dimension et de capacité",
                                "valeur" => "normes-de-dimension-et-de-capacite",
                                "description" => null
                            ],
                            [
                                "libelle" => "Irrigation à haut rendement",
                                "valeur" => "irrigation-a-haut-rendement",
                                "description" => null
                            ],
                            [
                                "libelle" => "Gestion des ressources en eau",
                                "valeur" => "gestion-des-ressources-en-eau",
                                "description" => null
                            ],
                            [
                                "libelle" => "Planification de l'exploitation et de la maintenance des infrastructures",
                                "valeur" => "planification-de-l-exploitation-et-de-la-maintenance-des-infrastructures",
                                "description" => null
                            ],
                            [
                                "libelle" => "Plan directeur et utilisation des terres",
                                "valeur" => "plan-directeur-et-utilisation-des-terres",
                                "description" => null
                            ],
                        ]
                    ],
                    [
                        "secteur_id" => 15,
                        "mesures" => [
                            [
                                "libelle" => "Irrigation à haut rendement",
                                "valeur" => "irrigation-a-haut-rendement",
                                "description" => ""
                            ],
                            [
                                "libelle" => "Composants éoliens/solaires/TIC résistants à la chaleur",
                                "valeur" => "irrigation-a-haut-rendement",
                                "description" => ""
                            ],
                            [
                                "libelle" => "Gestion de la demande / Réduction de l'ENC",
                                "valeur" => "irrigation-a-haut-rendement",
                                "description" => ""
                            ],
                        ]
                    ],
                    [
                        "secteur_id" => 11,
                        "mesures" => [
                            [
                                "libelle" => "Augmenter les réservoirs de stockage d'eau",
                                "description" => "Augmenter les réservoirs de stockage d'eau"
                            ],
                            [
                                "libelle" => "Repenser les installations de refroidissement",
                                "description" => "Repenser les installations de refroidissement"
                            ],
                            [
                                "libelle" => "Développer de nouvelles sources d'eau",
                                "description" => "Développer de nouvelles sources d'eau"
                            ],
                        ]
                    ],
                ]
            ],
            [
                "intitule" => "Inondations",
                "description" => "",
                "ponderation" => 25,
                "is_mandatory" => true,
                "secteurs" => [
                    [
                        "secteur_id" => 47,
                        "mesures" => [
                            [
                                "libelle" => "Drainage et conservation des sols",
                                "description" => "Drainage et conservation des sols"
                            ],
                            [
                                "libelle" => "Gestion environnementale",
                                "description" => "Gestion environnementale"
                            ],
                        ]
                    ],
                    [
                        "secteur_id" => 15,
                        "mesures" => [
                            [
                                "libelle" => "Construire/agrandir des réservoirs",
                                "description" => "Construire/agrandir des réservoirs"
                            ],
                            [
                                "libelle" => "Construire des digues et des déversoirs",
                                "description" => "Construire des digues et des déversoirs"
                            ],
                            [
                                "libelle" => "Déplacer le stockage de carburant hors des zones inondables",
                                "description" => "Déplacer le stockage de carburant hors des zones inondables"
                            ],
                        ]
                    ],
                    [
                        "secteur_id" => 11,
                        "mesures" => [
                            [
                                "libelle" => "Déplacement des infrastructures inondées",
                                "description" => "Déplacement des infrastructures inondées"
                            ],
                        ]
                    ],
                ]
            ],
            [
                "intitule" => "Élévation du niveau de la mer",
                "description" => "",
                "ponderation" => 25,
                "is_mandatory" => true,
                "secteurs" => [
                    [
                        "secteur_id" => 47,
                        "mesures" => [
                            [
                                "libelle" => "Systèmes d'information",
                                "description" => "Systèmes d'information"
                            ],
                            [
                                "libelle" => "Ouvrages d'art de protection",
                                "description" => "Ouvrages d'art de protection"
                            ],
                            [
                                "libelle" => "Formation",
                                "description" => "Formation"
                            ],
                        ]
                    ],
                    [
                        "secteur_id" => 15,
                        "mesures" => [
                            [
                                "libelle" => "Développer le contrôle des inondations",
                                "description" => "Développer le contrôle des inondations"
                            ],
                            [
                                "libelle" => "Renforcer les défenses côtières",
                                "description" => "Renforcer les défenses côtières"
                            ],
                            [
                                "libelle" => "Améliorer le drainage / rediriger les conduites d'eau",
                                "description" => "Améliorer le drainage / rediriger les conduites d'eau"
                            ],
                        ]
                    ],
                    [
                        "secteur_id" => 11,
                        "mesures" => [
                            [
                                "libelle" => "Prévenir l'intrusion d'eau salée dans les zones côtières",
                                "description" => "Prévenir l'intrusion d'eau salée dans les zones côtières"
                            ],
                            [
                                "libelle" => "Ajuster le niveau de traitement des eaux usées à la capacité de dilution révisée du point de rejet",
                                "description" => "Ajuster le niveau de traitement des eaux usées à la capacité de dilution révisée du point de rejet"
                            ],
                        ]
                    ],
                ]
            ],
        ]
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();
        
        try {
            // Créer la catégorie critère pour la checklist des mesures d'adaptation
            $categorieCritere = \App\Models\CategorieCritere::firstOrCreate([
                'slug' => $this->payload['slug'],
            ], [
                'type' => $this->payload['type'],
                'slug' => $this->payload['slug'],
                'is_mandatory' => $this->payload['is_mandatory']
            ]);

            // Créer ou récupérer les secteurs nécessaires
            $secteursMapping = $this->createOrGetSecteurs();

            // Traiter chaque critère du payload
            foreach ($this->payload['criteres'] as $critereData) {
                $critere = $this->createOrUpdateCritere($categorieCritere, $critereData);
                $this->createMesuresForCritere($critere, $critereData['secteurs'], $secteursMapping, $categorieCritere);
            }
            
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Créer ou récupérer les secteurs nécessaires
     */
    private function createOrGetSecteurs(): array
    {
        $secteurs = [];
        
        // Mapping des IDs de secteurs du payload avec les noms
        $secteursInfo = [
            47 => 'Agriculture',
            15 => 'Energie', 
            11 => 'Eau'
        ];

        foreach ($secteursInfo as $oldId => $nom) {
            $slug = SlugHelper::generate($nom);
            
            $secteur = Secteur::firstOrCreate([
                'slug' => $slug,
                'type' => 'secteur'
            ], [
                'nom' => $nom,
                'slug' => $slug,
                'type' => 'secteur',
                'description' => $nom
            ]);
            
            $secteurs[$oldId] = $secteur->id;
        }

        return $secteurs;
    }

    /**
     * Créer ou mettre à jour un critère
     */
    private function createOrUpdateCritere($categorieCritere, array $critereData): \App\Models\Critere
    {
        try {
            // Utiliser updateOrCreate avec une gestion d'exception
            return \App\Models\Critere::updateOrCreate([
                'intitule' => $critereData['intitule'],
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'ponderation' => $critereData['ponderation'],
                'commentaire' => $critereData['description'] ?? '',
                'is_mandatory' => $critereData['is_mandatory'] ?? true
            ]);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            // Si erreur de contrainte d'unicité, récupérer le critère existant
            return \App\Models\Critere::where('intitule', $critereData['intitule'])
                ->where('categorie_critere_id', $categorieCritere->id)
                ->first();
        }
    }

    /**
     * Créer les mesures pour un critère
     */
    private function createMesuresForCritere(\App\Models\Critere $critere, array $secteurs, array $secteursMapping, $categorieCritere): void
    {
        foreach ($secteurs as $secteurData) {
            $secteurId = $secteursMapping[$secteurData['secteur_id']] ?? null;
            
            if (!$secteurId) {
                continue; // Secteur non trouvé, passer au suivant
            }

            foreach ($secteurData['mesures'] as $mesureData) {
                // Générer la valeur si elle n'existe pas
                $valeur = $mesureData['valeur'] ?? SlugHelper::generate($mesureData['libelle']);
                
                \App\Models\Notation::updateOrCreate([
                    'libelle' => $mesureData['libelle'],
                    'secteur_id' => $secteurId,
                    'critere_id' => $critere->id,
                    'categorie_critere_id' => $categorieCritere->id
                ], [
                    'valeur' => $valeur,
                    'commentaire' => $mesureData['description'] ?? ''
                ]);
            }
        }
    }
}
