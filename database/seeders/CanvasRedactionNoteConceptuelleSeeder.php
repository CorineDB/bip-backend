<?php

namespace Database\Seeders;

use App\Models\Document;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanvasRedactionNoteConceptuelleSeeder extends Seeder
{
    protected $documentData = [
        "nom" => "Canevas de rédaction de la note conceptuelle",
        "slug" => "canevas-redaction-note-conceptuelle",
        "description" => "Formulaire de rédaction d'une note conceptuelle de projet",
        "type" => "formulaire",
        "champs" => [
            [
                "label" => "Contexte et justification",
                "info" => "",
                "key" => "contexte_justification",
                "attribut" => "contexte_justification",
                "placeholder" => "Décrivez le contexte et la justification du projet",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => false,
                "ordre_affichage" => 1,
                "type_champ" => "textarea",
                "meta_options" => [
                    "configs" => [
                        "max_length" => 3000,
                        "min_length" => 50,
                        "rows" => 4
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "required" => true,
                        "string" => true,
                        "max" => 3000,
                        "min" => 50
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => null
            ],
            [
                "label" => "Objectifs du projet",
                "info" => "",
                "key" => "objectifs_projet",
                "attribut" => "objectifs_projet",
                "placeholder" => "Définissez les objectifs du projet",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => false,
                "ordre_affichage" => 2,
                "type_champ" => "textarea",
                "meta_options" => [
                    "configs" => [
                        "max_length" => 2500,
                        "min_length" => 30,
                        "rows" => 4
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "required" => true,
                        "string" => true,
                        "max" => 2500,
                        "min" => 30
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => null
            ],
            [
                "label" => "Résultats attendus du projet",
                "info" => "",
                "key" => "resultats_attendus",
                "attribut" => "resultats_attendus",
                "placeholder" => "Décrivez les résultats attendus",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => false,
                "ordre_affichage" => 3,
                "type_champ" => "textarea",
                "meta_options" => [
                    "configs" => [
                        "max_length" => 2500,
                        "min_length" => 30,
                        "rows" => 4
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "required" => true,
                        "string" => true,
                        "max" => 2500,
                        "min" => 30
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => null
            ],
            [
                "label" => "Les livrables du processus d'élaboration du projet",
                "info" => "",
                "key" => "livrables_processus",
                "attribut" => "livrables_processus",
                "placeholder" => "Listez les livrables attendus",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => false,
                "ordre_affichage" => 5,
                "type_champ" => "textarea",
                "meta_options" => [
                    "configs" => [
                        "max_length" => 2000,
                        "min_length" => 30,
                        "rows" => 3
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "required" => true,
                        "string" => true,
                        "max" => 2000,
                        "min" => 30
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => null
            ],
            [
                "label" => "Pilotage et gouvernance du projet",
                "info" => "",
                "key" => "pilotage_gouvernance",
                "attribut" => "pilotage_gouvernance",
                "placeholder" => "Décrivez le pilotage et la gouvernance",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => false,
                "ordre_affichage" => 7,
                "type_champ" => "textarea",
                "meta_options" => [
                    "configs" => [
                        "max_length" => 2000,
                        "min_length" => 30,
                        "rows" => 3
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "required" => true,
                        "string" => true,
                        "max" => 2000,
                        "min" => 30
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => null
            ],
            [
                "label" => "Chronogramme du processus",
                "info" => "",
                "key" => "chronogramme_processus",
                "attribut" => "chronogramme_processus",
                "placeholder" => "Décrivez le chronogramme",
                "is_required" => true,
                "default_value" => null,
                "isEvaluated" => false,
                "ordre_affichage" => 8,
                "type_champ" => "textarea",
                "meta_options" => [
                    "configs" => [
                        "max_length" => 2000,
                        "min_length" => 30,
                        "rows" => 3
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => true,
                        "conditions" => []
                    ],
                    "validations_rules" => [
                        "required" => true,
                        "string" => true,
                        "max" => 2000,
                        "min" => 30
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => null
            ]
        ],
        "sections" => [
            [
                "key" => "demarche-conduite-processus",
                "intitule" => "Démarche de conduite du processus d'élaboration du projet",
                "description" => "Processus d'élaboration du projet",
                "ordre_affichage" => 4,
                "type" => "formulaire",
                "champs" => [
                    [
                        "label" => "Démarche administrative",
                        "info" => "",
                        "key" => "demarche_administrative",
                        "attribut" => "demarche_administrative",
                        "placeholder" => "Décrivez la démarche administrative",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 20,
                                "rows" => 2
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => true,
                                "string" => true,
                                "max" => 1500,
                                "min" => 20
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Démarche technique",
                        "info" => "",
                        "key" => "demarche_technique",
                        "attribut" => "demarche_technique",
                        "placeholder" => "Décrivez la démarche technique",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 2,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 20,
                                "rows" => 2
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => true,
                                "string" => true,
                                "max" => 1500,
                                "min" => 20
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
                        "placeholder" => "Identifiez les parties prenantes",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 3,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 20,
                                "rows" => 2
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => true,
                                "string" => true,
                                "max" => 1500,
                                "min" => 20
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ]
                ]
            ],
            [
                "key" => "coherence-strategique",
                "intitule" => "Cohérence du projet avec le PAG ou la stratégie sectorielle",
                "description" => "Cohérence avec les cadres stratégiques",
                "ordre_affichage" => 6,
                "type" => "formulaire",
                "champs" => []
            ],
            [
                "key" => "budget-et-financement",
                "intitule" => "Budget et sources de financement du projet",
                "description" => "Aspects financiers du projet",
                "ordre_affichage" => 9,
                "type" => "formulaire",
                "champs" => [
                    [
                        "label" => "Budget détaillé du processus",
                        "info" => "",
                        "key" => "budget_detaille",
                        "attribut" => "budget_detaille",
                        "placeholder" => "Présentez le budget détaillé",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 1,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 2000,
                                "min_length" => 50,
                                "rows" => 2
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => true,
                                "string" => true,
                                "max" => 2000,
                                "min" => 50
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ],
                    [
                        "label" => "Coût estimatif du projet",
                        "info" => "",
                        "key" => "cout_estimatif",
                        "attribut" => "cout_estimatif",
                        "placeholder" => "Indiquez le coût estimatif global",
                        "is_required" => true,
                        "default_value" => null,
                        "isEvaluated" => false,
                        "ordre_affichage" => 2,
                        "type_champ" => "textarea",
                        "meta_options" => [
                            "configs" => [
                                "max_length" => 1500,
                                "min_length" => 20,
                                "rows" => 2
                            ],
                            "conditions" => [
                                "disable" => false,
                                "visible" => true,
                                "conditions" => []
                            ],
                            "validations_rules" => [
                                "required" => true,
                                "string" => true,
                                "max" => 1500,
                                "min" => 20
                            ]
                        ],
                        "champ_standard" => true,
                        "startWithNewLine" => null
                    ]
                ],
                "sous_sections" => [
                    [
                        "key" => "sources-financement",
                        "intitule" => "Sources de financement",
                        "description" => "Sources de financement du projet",
                        "ordre_affichage" => 3,
                        "type" => "formulaire",
                        "champs" => [
                            [
                                "label" => "Types de financement",
                                "info" => "Sélectionnez les types de financement",
                                "key" => "types_financement",
                                "attribut" => "types_financement",
                                "placeholder" => "Choisissez les types de financement",
                                "is_required" => true,
                                "default_value" => null,
                                "isEvaluated" => false,
                                "ordre_affichage" => 1,
                                "type_champ" => "select",
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [],
                                        "multiple" => true,
                                        "load_dynamic" => true,
                                        "datasource" => "/api/financements?filter_type=type",
                                        "filter_type" => "type"
                                    ],
                                    "conditions" => [
                                        "disable" => false,
                                        "visible" => true,
                                        "conditions" => []
                                    ],
                                    "validations_rules" => [
                                        "required" => true,
                                        "array" => true,
                                        "min" => 1
                                    ]
                                ],
                                "champ_standard" => true,
                                "startWithNewLine" => null
                            ],
                            [
                                "label" => "Natures de financement",
                                "info" => "Sélectionnez les natures de financement",
                                "key" => "natures_financement",
                                "attribut" => "natures_financement",
                                "placeholder" => "Choisissez les natures de financement",
                                "is_required" => false,
                                "default_value" => null,
                                "isEvaluated" => false,
                                "ordre_affichage" => 2,
                                "type_champ" => "select",
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [],
                                        "multiple" => true,
                                        "load_dynamic" => true,
                                        "datasource" => "/api/financements?filter_type=nature",
                                        "filter_type" => "nature",
                                        "depends_on" => "types_financement",
                                        "dynamic_params" => [
                                            "parent_id" => "{type_financement}"
                                        ]
                                    ],
                                    "conditions" => [
                                        "disable" => false,
                                        "visible" => true,
                                        "conditions" => [
                                            [
                                                "field" => "types_financement",
                                                "operator" => "not_empty"
                                            ]
                                        ]
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
                                "info" => "Sélectionnez les sources de financement spécifiques",
                                "key" => "sources_financement",
                                "attribut" => "sources_financement",
                                "placeholder" => "Choisissez les sources de financement",
                                "is_required" => false,
                                "default_value" => null,
                                "isEvaluated" => false,
                                "ordre_affichage" => 3,
                                "type_champ" => "select",
                                "meta_options" => [
                                    "configs" => [
                                        "options" => [],
                                        "multiple" => true,
                                        "load_dynamic" => true,
                                        "datasource" => "/api/financements?filter_type=source",
                                        "filter_type" => "source",
                                        "depends_on" => "natures_financement",
                                        "dynamic_params" => [
                                            "parent_id" => "{nature_financement}"
                                        ]
                                    ],
                                    "conditions" => [
                                        "disable" => false,
                                        "visible" => true,
                                        "conditions" => [
                                            [
                                                "field" => "natures_financement",
                                                "operator" => "not_empty"
                                            ]
                                        ]
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
        // Créer ou récupérer la catégorie de document
        $categorieDocument = \App\Models\CategorieDocument::firstOrCreate([
            'slug' => "canevas-redaction-note-conceptuelle",
        ], [
            'nom' => "Canevas de rédaction de note conceptuelle",
            'slug' => "canevas-redaction-note-conceptuelle",
            "description" => "Formulaire standard de rédaction de note conceptuelle",
            "format" => "document"
        ]);

        // Extraire les données relationnelles avant création
        $sectionsData = $this->documentData['sections'] ?? [];

        // Nettoyer les données du document principal
        $documentData = collect($this->documentData)->except(['sections', 'champs', 'id'])->toArray();

        $documentData = array_merge($documentData, [
            "categorieId" => $categorieDocument->id
        ]);

        // Utiliser upsert pour gérer les conflits
        try {
            $document = Document::firstOrNew(['slug' => 'canevas-redaction-note-conceptuelle']);

            // Si le document existe déjà, on le met à jour
            if ($document->exists) {
                $document->fill($documentData);
                $document->save();
            } else {
                // Sinon, on essaie de le créer avec un nom unique si nécessaire
                $originalNom = $documentData['nom'];
                $counter = 1;

                while (true) {
                    try {
                        $document->fill($documentData);
                        $document->save();
                        break;
                    } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
                        // Si conflit sur le nom, essayer avec un suffixe
                        $documentData['nom'] = $originalNom . ' (' . $counter . ')';
                        $counter++;

                        // Éviter une boucle infinie
                        if ($counter > 10) {
                            throw $e;
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // En dernier recours, forcer la suppression et recréer
            DB::statement('DELETE FROM documents WHERE nom = ? OR slug = ?', [
                'Canevas de rédaction de la note conceptuelle',
                'canevas-redaction-note-conceptuelle'
            ]);

            $document = Document::create($documentData);
        }

        // Supprimer complètement les sections et champs existants pour ce document
        DB::statement('DELETE FROM champs WHERE "documentId" = ?', [$document->id]);
        DB::statement('DELETE FROM champs_sections WHERE "documentId" = ?', [$document->id]);

        // Créer les champs généraux du document
        if (isset($this->documentData['champs']) && is_array($this->documentData['champs'])) {
            foreach ($this->documentData['champs'] as $champData) {
                $this->createChamp($champData, $document, null);
            }
        }

        // Traiter les sections avec leurs champs
        if (!empty($sectionsData)) {
            $this->createSectionsWithChamps($document, $sectionsData);
        }

        // Créer les sections de programmes dynamiques comme sous-sections
        $this->createProgrammeSections($document);
    }

    /**
     * Créer directement les sections de programmes comme sous-sections de coherence-strategique
     */
    private function createProgrammeSections($document): void
    {
        // Trouver la section coherence-strategique en base
        $coherenceSection = $document->sections()->where('slug', 'coherence-strategique')->first();

        if (!$coherenceSection) {
            return; // Si la section n'existe pas, on ne peut pas ajouter les sous-sections
        }

        // Récupérer tous les programmes (typeId = NULL) avec toute leur descendance
        $programmes = \App\Models\TypeProgramme::whereNull('typeId')
            ->with(['children' => function ($query) {
                $this->loadChildrenRecursively($query, 10); // Charger jusqu'à 10 niveaux
            }])
            ->orderBy('type_programme')
            ->get();

        $ordreAffichage = 1;

        foreach ($programmes as $programme) {
            // Créer directement la sous-section en base
            $programmeSection = $document->sections()->create([
                'intitule' => $programme->type_programme,
                'description' => "Composants du programme " . $programme->type_programme,
                'ordre_affichage' => $ordreAffichage,
                'type' => 'formulaire',
                'slug' => 'programme-' . $programme->slug,
                'parentSectionId' => $coherenceSection->id
            ]);

            // Créer les champs hiérarchiques avec dépendances
            $this->createHierarchicalFields($programme, $document, $programmeSection);

            $ordreAffichage++;
        }
    }

    /**
     * Ajouter les sections dynamiques pour les programmes comme sous-sections de coherence-strategique
     */
    private function addProgrammeSections(array $sectionsData): array
    {
        // Récupérer tous les programmes (typeId = NULL) avec toute leur descendance
        $programmes = \App\Models\TypeProgramme::whereNull('typeId')
            ->with(['children' => function ($query) {
                $this->loadChildrenRecursively($query, 10); // Charger jusqu'à 10 niveaux
            }])
            ->orderBy('type_programme')
            ->get();

        // Trouver la section coherence-strategique et lui ajouter les sous-sections de programmes
        foreach ($sectionsData as &$section) {
            if ($section['key'] === 'coherence-strategique') {
                // Initialiser les sous-sections si elles n'existent pas
                if (!isset($section['sous_sections'])) {
                    $section['sous_sections'] = [];
                }

                $ordreAffichage = 1;

                foreach ($programmes as $programme) {
                    $sousSectionData = $this->createProgrammeSectionRecursive($programme, $ordreAffichage);

                    if ($sousSectionData) {
                        $section['sous_sections'][] = $sousSectionData;
                        $ordreAffichage++;
                    }
                }
                break; // Sortir de la boucle une fois qu'on a trouvé et modifié la section coherence-strategique
            }
        }

        return $sectionsData;
    }

    /**
     * Méthode helper pour charger récursivement les enfants
     */
    private function loadChildrenRecursively($query, $depth)
    {
        if ($depth > 0) {
            $query->with(['children' => function ($q) use ($depth) {
                $this->loadChildrenRecursively($q, $depth - 1);
            }]);
        }
    }

    /**
     * Créer une section pour un programme avec tous ses composants comme champs
     */
    private function createProgrammeSectionRecursive($programme, $ordreAffichage)
    {
        $sectionKey = 'programme-' . $programme->slug;

        $sectionData = [
            "key" => $sectionKey,
            "intitule" => $programme->type_programme,
            "description" => "Composants du programme " . $programme->type_programme,
            "ordre_affichage" => $ordreAffichage,
            "type" => "formulaire",
            "champs" => []
        ];

        // Collecter tous les descendants du programme et créer un champ pour chacun
        $allDescendants = $this->collectAllDescendants($programme);

        $champOrdre = 1;
        foreach ($allDescendants as $descendant) {
            $champData = [
                "label" => $descendant->type_programme,
                "info" => "Sélectionnez les éléments de " . $descendant->type_programme,
                "key" => $descendant->slug,
                "attribut" => $descendant->slug,
                "placeholder" => "Choisissez les éléments de " . $descendant->type_programme,
                "is_required" => false,
                "default_value" => null,
                "isEvaluated" => false,
                "ordre_affichage" => $champOrdre,
                "type_champ" => "select",
                "meta_options" => [
                    "configs" => [
                        "options" => [],
                        "multiple" => true,
                        "load_dynamic" => true,
                        "datasource" => "/api/types-programmes?typeId=" . $descendant->id,
                        "filter_field" => "typeId",
                        "filter_value" => $descendant->id
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
            ];

            $sectionData["champs"][] = $champData;
            $champOrdre++;
        }

        // Retourner la section seulement si elle a des champs
        if (!empty($sectionData["champs"])) {
            return $sectionData;
        }

        return null;
    }

    /**
     * Créer les champs hiérarchiques avec dépendances et conditions
     */
    private function createHierarchicalFields($programme, $document, $programmeSection, $parent = null, $champOrdre = 1)
    {
        // Récupérer les enfants directs
        $enfants = $programme->children ?? collect();

        foreach ($enfants as $enfant) {
            // Déterminer les dépendances et conditions
            $dependsOn = $parent ? $parent->slug : null;
            $conditions = $this->buildFieldConditions($parent);

            $champData = [
                "label" => $enfant->type_programme,
                "info" => "Sélectionnez les éléments de " . $enfant->type_programme,
                "key" => $enfant->slug,
                "attribut" => $enfant->slug,
                "placeholder" => "Choisissez les éléments de " . $enfant->type_programme,
                "is_required" => false,
                "default_value" => null,
                "isEvaluated" => false,
                "ordre_affichage" => $champOrdre,
                "type_champ" => "select",
                "meta_options" => [
                    "configs" => [
                        "options" => [],
                        "multiple" => true,
                        "load_dynamic" => true,
                        "datasource" => "/api/programmes/{$programme->id}/composants-programme/{$enfant->id}/composants",
                        "depends_on" => $dependsOn
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => $parent ? false : true, // Masqué si dépend d'un parent
                        "conditions" => $conditions
                    ],
                    "validations_rules" => [
                        "required" => false,
                        "array" => true,
                        "min" => 0
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => null
            ];

            $this->createChamp($champData, $document, $programmeSection);

            // Créer récursivement les champs enfants
            if ($enfant->children && $enfant->children->isNotEmpty()) {
                $champOrdre = $this->createHierarchicalFieldsForChildren($enfant, $programme, $document, $programmeSection, $enfant, $champOrdre + 1);
            } else {
                $champOrdre++;
            }
        }

        return $champOrdre;
    }

    /**
     * Créer les champs pour les enfants d'un élément
     */
    private function createHierarchicalFieldsForChildren($element, $programmeRacine, $document, $programmeSection, $parent, $champOrdre)
    {
        foreach ($element->children as $enfant) {
            $dependsOn = $parent ? $parent->slug : null;
            $conditions = $this->buildFieldConditions($parent);

            $champData = [
                "label" => $enfant->type_programme,
                "info" => "Sélectionnez les éléments de " . $enfant->type_programme,
                "key" => $enfant->slug,
                "attribut" => $enfant->slug,
                "placeholder" => "Choisissez les éléments de " . $enfant->type_programme,
                "is_required" => false,
                "default_value" => null,
                "isEvaluated" => false,
                "ordre_affichage" => $champOrdre,
                "type_champ" => "select",
                "meta_options" => [
                    "configs" => [
                        "options" => [],
                        "multiple" => true,
                        "load_dynamic" => true,
                        "datasource" => "/api/programmes/{$programmeRacine->id}/composants-programme/{$enfant->id}/composants",
                        "depends_on" => $dependsOn
                    ],
                    "conditions" => [
                        "disable" => false,
                        "visible" => false, // Masqué par défaut car dépend d'un parent
                        "conditions" => $conditions
                    ],
                    "validations_rules" => [
                        "required" => false,
                        "array" => true,
                        "min" => 0
                    ]
                ],
                "champ_standard" => true,
                "startWithNewLine" => null
            ];

            $this->createChamp($champData, $document, $programmeSection);
            $champOrdre++;

            // Continuer récursivement
            if ($enfant->children && $enfant->children->isNotEmpty()) {
                $champOrdre = $this->createHierarchicalFieldsForChildren($enfant, $programmeRacine, $document, $programmeSection, $enfant, $champOrdre);
            }
        }

        return $champOrdre;
    }

    /**
     * Construire les conditions d'affichage pour un champ
     */
    private function buildFieldConditions($parent)
    {
        if (!$parent) {
            return []; // Champ racine, pas de conditions
        }

        return [
            [
                "field" => $parent->slug,
                "operator" => "not_empty"
            ]
        ];
    }

    /**
     * Collecter récursivement tous les descendants d'un élément
     */
    private function collectAllDescendants($element)
    {
        $descendants = collect();

        if ($element->children && $element->children->isNotEmpty()) {
            foreach ($element->children as $enfant) {
                $descendants->push($enfant);
                // Collecter récursivement les descendants de cet enfant
                $descendants = $descendants->merge($this->collectAllDescendants($enfant));
            }
        }

        return $descendants;
    }

    /**
     * Réorganiser les sections par ordre d'affichage
     */
    private function reorderSections(array $sectionsData): array
    {
        usort($sectionsData, function ($a, $b) {
            return $a['ordre_affichage'] <=> $b['ordre_affichage'];
        });

        return $sectionsData;
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
                'type' => $sectionData['type'] ?? null,
                'slug' => $sectionData['key'] ?? \Illuminate\Support\Str::slug($sectionData['intitule'])
            ]);

            // Créer les champs de cette section si fournis
            if (isset($sectionData['champs']) && is_array($sectionData['champs'])) {
                foreach ($sectionData['champs'] as $champData) {
                    $this->createChamp($champData, $document, $section);
                }
            }

            // Créer les sous-sections si elles existent
            if (isset($sectionData['sous_sections']) && is_array($sectionData['sous_sections'])) {
                foreach ($sectionData['sous_sections'] as $sousSectionData) {
                    $this->createSousSection($sousSectionData, $document, $section);
                }
            }
        }
    }

    /**
     * Créer une sous-section avec ses champs
     */
    private function createSousSection(array $sousSectionData, $document, $parentSection): void
    {
        $sousSection = $document->sections()->create([
            'intitule' => $sousSectionData['intitule'],
            'description' => $sousSectionData['description'],
            'ordre_affichage' => $sousSectionData['ordre_affichage'],
            'type' => $sousSectionData['type'] ?? null,
            'slug' => $sousSectionData['key'] ?? \Illuminate\Support\Str::slug($sousSectionData['intitule']),
            'parentSectionId' => $parentSection->id
        ]);

        // Créer les champs de cette sous-section si fournis
        if (isset($sousSectionData['champs']) && is_array($sousSectionData['champs'])) {
            foreach ($sousSectionData['champs'] as $champData) {
                $this->createChamp($champData, $document, $sousSection);
            }
        }

        // Récursion pour les sous-sous-sections (si nécessaire)
        if (isset($sousSectionData['sous_sections']) && is_array($sousSectionData['sous_sections'])) {
            foreach ($sousSectionData['sous_sections'] as $sousSousSectionData) {
                $this->createSousSection($sousSousSectionData, $document, $sousSection);
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

    /*private $payload = {
    "nom": "Canevas de rédaction de la note conceptuelle",
    "description": "Formulaire de rédaction d'une note conceptuelle de projet",
    "type": "formulaire",
    "categorieId": 2,
    "forms": [
        {
            "element_type": "field",
            "ordre_affichage": 1,
            "id": 189,
            "label": "Contexte et justification",
            "info": "",
            "attribut": "contexte_justification",
            "placeholder": "Décrivez le contexte et la justification du projet",
            "is_required": true,
            "default_value": null,
            "isEvaluated": false,
            "type_champ": "textarea",
            "sectionId": null,
            "documentId": 15,
            "meta_options": {
                "configs": {
                    "rows": 4,
                    "max_length": 3000,
                    "min_length": 50
                },
                "conditions": {
                    "disable": false,
                    "visible": true,
                    "conditions": []
                },
                "validations_rules": {
                    "max": 3000,
                    "min": 50,
                    "string": true,
                    "required": true
                }
            },
            "champ_standard": false,
            "startWithNewLine": null
        },
        {
            "element_type": "field",
            "ordre_affichage": 2,
            "id": 190,
            "label": "Objectifs du projet",
            "info": "",
            "attribut": "objectifs_projet",
            "placeholder": "Définissez les objectifs du projet",
            "is_required": true,
            "default_value": null,
            "isEvaluated": false,
            "type_champ": "textarea",
            "sectionId": null,
            "documentId": 15,
            "meta_options": {
                "configs": {
                    "rows": 4,
                    "max_length": 2500,
                    "min_length": 30
                },
                "conditions": {
                    "disable": false,
                    "visible": true,
                    "conditions": []
                },
                "validations_rules": {
                    "max": 2500,
                    "min": 30,
                    "string": true,
                    "required": true
                }
            },
            "champ_standard": false,
            "startWithNewLine": null
        },
        {
            "element_type": "field",
            "ordre_affichage": 3,
            "id": 191,
            "label": "Résultats attendus du projet",
            "info": "",
            "attribut": "resultats_attendus",
            "placeholder": "Décrivez les résultats attendus",
            "is_required": true,
            "default_value": null,
            "isEvaluated": false,
            "type_champ": "textarea",
            "sectionId": null,
            "documentId": 15,
            "meta_options": {
                "configs": {
                    "rows": 4,
                    "max_length": 2500,
                    "min_length": 30
                },
                "conditions": {
                    "disable": false,
                    "visible": true,
                    "conditions": []
                },
                "validations_rules": {
                    "max": 2500,
                    "min": 30,
                    "string": true,
                    "required": true
                }
            },
            "champ_standard": false,
            "startWithNewLine": null
        },
        {
            "element_type": "section",
            "ordre_affichage": 4,
            "id": 62,
            "key": "demarche-conduite-processus",
            "intitule": "Démarche de conduite du processus d 'élaboration du projet",
            "description": "Processus d'élaboration du projet",
            "type": "formulaire",
            "parentSectionId": null,
            "elements": [
                {
                    "element_type": "field",
                    "ordre_affichage": 1,
                    "id": 195,
                    "label": "Démarche administrative",
                    "info": "",
                    "attribut": "demarche_administrative",
                    "placeholder": "Décrivez la démarche administrative",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "type_champ": "textarea",
                    "sectionId": 62,
                    "documentId": 15,
                    "meta_options": {
                        "configs": {
                            "rows": 2,
                            "max_length": 1500,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "max": 1500,
                            "min": 20,
                            "string": true,
                            "required": true
                        }
                    },
                    "champ_standard": false,
                    "startWithNewLine": null
                },
                {
                    "element_type": "field",
                    "ordre_affichage": 2,
                    "id": 196,
                    "label": "Démarche technique",
                    "info": "",
                    "attribut": "demarche_technique",
                    "placeholder": "Décrivez la démarche technique",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "type_champ": "textarea",
                    "sectionId": 62,
                    "documentId": 15,
                    "meta_options": {
                        "configs": {
                            "rows": 2,
                            "max_length": 1500,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "max": 1500,
                            "min": 20,
                            "string": true,
                            "required": true
                        }
                    },
                    "champ_standard": false,
                    "startWithNewLine": null
                },
                {
                    "element_type": "field",
                    "ordre_affichage": 3,
                    "id": 197,
                    "label": "Parties prenantes",
                    "info": "",
                    "attribut": "parties_prenantes",
                    "placeholder": "Identifiez les parties prenantes",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "type_champ": "textarea",
                    "sectionId": 62,
                    "documentId": 15,
                    "meta_options": {
                        "configs": {
                            "rows": 2,
                            "max_length": 1500,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "max": 1500,
                            "min": 20,
                            "string": true,
                            "required": true
                        }
                    },
                    "champ_standard": false,
                    "startWithNewLine": null
                },
                {
                    "element_type": "field",
                    "ordre_affichage": 5,
                    "label": "Les livrables du processus d'élaboration du projet",
                    "info": "",
                    "attribut": "livrables_processus",
                    "placeholder": "Listez les livrables attendus",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "type_champ": "textarea",
                    "documentId": 15,
                    "meta_options": {
                        "configs": {
                            "rows": 3,
                            "max_length": 2000,
                            "min_length": 30
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "max": 2000,
                            "min": 30,
                            "string": true,
                            "required": true
                        }
                    },
                    "champ_standard": false,
                    "startWithNewLine": null
                }
            ]
        },
        {
            "element_type": "field",
            "ordre_affichage": 5,
            "id": 192,
            "label": "Les livrables du processus d'élaboration du projet",
            "info": "",
            "attribut": "livrables_processus",
            "placeholder": "Listez les livrables attendus",
            "is_required": true,
            "default_value": null,
            "isEvaluated": false,
            "type_champ": "textarea",
            "sectionId": null,
            "documentId": 15,
            "meta_options": {
                "configs": {
                    "rows": 3,
                    "max_length": 2000,
                    "min_length": 30
                },
                "conditions": {
                    "disable": false,
                    "visible": true,
                    "conditions": []
                },
                "validations_rules": {
                    "max": 2000,
                    "min": 30,
                    "string": true,
                    "required": true
                }
            },
            "champ_standard": false,
            "startWithNewLine": null
        },
        {
            "element_type": "section",
            "ordre_affichage": 6,
            "id": 63,
            "key": "coherence-strategique",
            "intitule": "Cohérence du projet avec le PAG ou la stratégie sectorielle",
            "description": "Cohérence avec les cadres stratégiques",
            "type": "formulaire",
            "parentSectionId": null,
            "elements": [
                {
                    "element_type": "section",
                    "ordre_affichage": 1,
                    "id": 66,
                    "key": "programme-pag",
                    "intitule": "Programme d 'Action du Gouvernement.",
                    "description": "Composants du programme Programme d'Action du Gouvernement.",
                    "type": "formulaire",
                    "parentSectionId": 63,
                    "elements": [
                        {
                            "element_type": "field",
                            "ordre_affichage": 1,
                            "id": 203,
                            "label": "Piliers du PAG",
                            "info": "Sélectionnez les éléments de Piliers du PAG",
                            "attribut": "pilier-pag",
                            "placeholder": "Choisissez les éléments de Piliers du PAG",
                            "is_required": false,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 66,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/programmes/1/composants-programme/2/composants",
                                    "depends_on": null,
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": true,
                                    "conditions": []
                                },
                                "validations_rules": {
                                    "min": 0,
                                    "array": true,
                                    "required": false
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        },
                        {
                            "element_type": "field",
                            "ordre_affichage": 2,
                            "id": 204,
                            "label": "Axes du PAG",
                            "info": "Sélectionnez les éléments de Axes du PAG",
                            "attribut": "axe-pag",
                            "placeholder": "Choisissez les éléments de Axes du PAG",
                            "is_required": false,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 66,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/programmes/1/composants-programme/3/composants",
                                    "depends_on": null,
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": true,
                                    "conditions": []
                                },
                                "validations_rules": {
                                    "min": 0,
                                    "array": true,
                                    "required": false
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        },
                        {
                            "element_type": "field",
                            "ordre_affichage": 3,
                            "id": 205,
                            "label": "Actions du PAG",
                            "info": "Sélectionnez les éléments de Actions du PAG",
                            "attribut": "action-pag",
                            "placeholder": "Choisissez les éléments de Actions du PAG",
                            "is_required": false,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 66,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/programmes/1/composants-programme/4/composants",
                                    "depends_on": "axe-pag",
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": false,
                                    "conditions": [
                                        {
                                            "field": "axe-pag",
                                            "operator": "not_empty"
                                        }
                                    ]
                                },
                                "validations_rules": {
                                    "min": 0,
                                    "array": true,
                                    "required": false
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        }
                    ]
                },
                {
                    "element_type": "section",
                    "ordre_affichage": 2,
                    "id": 67,
                    "key": "programme-pnd",
                    "intitule": "Programme de Developpement Durable.",
                    "description": "Composants du programme Programme de Developpement Durable.",
                    "type": "formulaire",
                    "parentSectionId": 63,
                    "elements": [
                        {
                            "element_type": "field",
                            "ordre_affichage": 1,
                            "id": 206,
                            "label": "Orientation stratégique du PND",
                            "info": "Sélectionnez les éléments de Orientation stratégique du PND",
                            "attribut": "orientation-strategique-pnd",
                            "placeholder": "Choisissez les éléments de Orientation stratégique du PND",
                            "is_required": false,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 67,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/programmes/5/composants-programme/6/composants",
                                    "depends_on": null,
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": true,
                                    "conditions": []
                                },
                                "validations_rules": {
                                    "min": 0,
                                    "array": true,
                                    "required": false
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        },
                        {
                            "element_type": "field",
                            "ordre_affichage": 2,
                            "id": 207,
                            "label": "Objectif stratégique du PND",
                            "info": "Sélectionnez les éléments de Objectif stratégique du PND",
                            "attribut": "objectif-strategique-pnd",
                            "placeholder": "Choisissez les éléments de Objectif stratégique du PND",
                            "is_required": false,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 67,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/programmes/5/composants-programme/7/composants",
                                    "depends_on": "orientation-strategique-pnd",
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": false,
                                    "conditions": [
                                        {
                                            "field": "orientation-strategique-pnd",
                                            "operator": "not_empty"
                                        }
                                    ]
                                },
                                "validations_rules": {
                                    "min": 0,
                                    "array": true,
                                    "required": false
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        },
                        {
                            "element_type": "field",
                            "ordre_affichage": 3,
                            "id": 208,
                            "label": "Resultats stratégique du PND",
                            "info": "Sélectionnez les éléments de Resultats stratégique du PND",
                            "attribut": "resultats-strategique-pnd",
                            "placeholder": "Choisissez les éléments de Resultats stratégique du PND",
                            "is_required": false,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 67,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/programmes/5/composants-programme/8/composants",
                                    "depends_on": "objectif-strategique-pnd",
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": false,
                                    "conditions": [
                                        {
                                            "field": "objectif-strategique-pnd",
                                            "operator": "not_empty"
                                        }
                                    ]
                                },
                                "validations_rules": {
                                    "min": 0,
                                    "array": true,
                                    "required": false
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        },
                        {
                            "element_type": "field",
                            "ordre_affichage": 4,
                            "id": 209,
                            "label": "Axes stratégique du PND",
                            "info": "Sélectionnez les éléments de Axes stratégique du PND",
                            "attribut": "axe-strategique-pnd",
                            "placeholder": "Choisissez les éléments de Axes stratégique du PND",
                            "is_required": false,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 67,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/programmes/5/composants-programme/9/composants",
                                    "depends_on": "objectif-strategique-pnd",
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": false,
                                    "conditions": [
                                        {
                                            "field": "objectif-strategique-pnd",
                                            "operator": "not_empty"
                                        }
                                    ]
                                },
                                "validations_rules": {
                                    "min": 0,
                                    "array": true,
                                    "required": false
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        }
                    ]
                }
            ]
        },
        {
            "element_type": "field",
            "ordre_affichage": 7,
            "id": 193,
            "label": "Pilotage et gouvernance du projet",
            "info": "",
            "attribut": "pilotage_gouvernance",
            "placeholder": "Décrivez le pilotage et la gouvernance",
            "is_required": true,
            "default_value": null,
            "isEvaluated": false,
            "type_champ": "textarea",
            "sectionId": null,
            "documentId": 15,
            "meta_options": {
                "configs": {
                    "rows": 3,
                    "max_length": 2000,
                    "min_length": 30
                },
                "conditions": {
                    "disable": false,
                    "visible": true,
                    "conditions": []
                },
                "validations_rules": {
                    "max": 2000,
                    "min": 30,
                    "string": true,
                    "required": true
                }
            },
            "champ_standard": false,
            "startWithNewLine": null
        },
        {
            "element_type": "field",
            "ordre_affichage": 8,
            "id": 194,
            "label": "Chronogramme du processus",
            "info": "",
            "attribut": "chronogramme_processus",
            "placeholder": "Décrivez le chronogramme",
            "is_required": true,
            "default_value": null,
            "isEvaluated": false,
            "type_champ": "textarea",
            "sectionId": null,
            "documentId": 15,
            "meta_options": {
                "configs": {
                    "rows": 3,
                    "max_length": 2000,
                    "min_length": 30
                },
                "conditions": {
                    "disable": false,
                    "visible": true,
                    "conditions": []
                },
                "validations_rules": {
                    "max": 2000,
                    "min": 30,
                    "string": true,
                    "required": true
                }
            },
            "champ_standard": false,
            "startWithNewLine": null
        },
        {
            "element_type": "section",
            "ordre_affichage": 9,
            "id": 64,
            "key": "budget-et-financement",
            "intitule": "Budget et sources de financement du projet",
            "description": "Aspects financiers du projet",
            "type": "formulaire",
            "parentSectionId": null,
            "elements": [
                {
                    "element_type": "field",
                    "ordre_affichage": 1,
                    "id": 198,
                    "label": "Budget détaillé du processus",
                    "info": "",
                    "attribut": "budget_detaille",
                    "placeholder": "Présentez le budget détaillé",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "type_champ": "textarea",
                    "sectionId": 64,
                    "documentId": 15,
                    "meta_options": {
                        "configs": {
                            "rows": 2,
                            "max_length": 2000,
                            "min_length": 50
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "max": 2000,
                            "min": 50,
                            "string": true,
                            "required": true
                        }
                    },
                    "champ_standard": false,
                    "startWithNewLine": null
                },
                {
                    "element_type": "field",
                    "ordre_affichage": 2,
                    "id": 199,
                    "label": "Coût estimatif du projet",
                    "info": "",
                    "attribut": "cout_estimatif",
                    "placeholder": "Indiquez le coût estimatif global",
                    "is_required": true,
                    "default_value": null,
                    "isEvaluated": false,
                    "type_champ": "textarea",
                    "sectionId": 64,
                    "documentId": 15,
                    "meta_options": {
                        "configs": {
                            "rows": 2,
                            "max_length": 1500,
                            "min_length": 20
                        },
                        "conditions": {
                            "disable": false,
                            "visible": true,
                            "conditions": []
                        },
                        "validations_rules": {
                            "max": 1500,
                            "min": 20,
                            "string": true,
                            "required": true
                        }
                    },
                    "champ_standard": false,
                    "startWithNewLine": null
                },
                {
                    "element_type": "section",
                    "ordre_affichage": 3,
                    "id": 65,
                    "key": "sources-financement",
                    "intitule": "Sources de financement",
                    "description": "Sources de financement du projet",
                    "type": "formulaire",
                    "parentSectionId": 64,
                    "elements": [
                        {
                            "element_type": "field",
                            "ordre_affichage": 1,
                            "id": 200,
                            "label": "Types de financement",
                            "info": "Sélectionnez les types de financement",
                            "attribut": "types_financement",
                            "placeholder": "Choisissez les types de financement",
                            "is_required": true,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 65,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/financements?filter_type=type",
                                    "filter_type": "type",
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": true,
                                    "conditions": []
                                },
                                "validations_rules": {
                                    "min": 1,
                                    "array": true,
                                    "required": true
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        },
                        {
                            "element_type": "field",
                            "ordre_affichage": 2,
                            "id": 201,
                            "label": "Natures de financement",
                            "info": "Sélectionnez les natures de financement",
                            "attribut": "natures_financement",
                            "placeholder": "Choisissez les natures de financement",
                            "is_required": false,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 65,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/financements?filter_type=nature",
                                    "depends_on": "types_financement",
                                    "filter_type": "nature",
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": true,
                                    "conditions": [
                                        {
                                            "field": "types_financement",
                                            "operator": "not_empty"
                                        }
                                    ]
                                },
                                "validations_rules": {
                                    "min": 0,
                                    "array": true,
                                    "required": false
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        },
                        {
                            "element_type": "field",
                            "ordre_affichage": 3,
                            "id": 202,
                            "label": "Sources de financement",
                            "info": "Sélectionnez les sources de financement spécifiques",
                            "attribut": "sources_financement",
                            "placeholder": "Choisissez les sources de financement",
                            "is_required": false,
                            "default_value": null,
                            "isEvaluated": false,
                            "type_champ": "select",
                            "sectionId": 65,
                            "documentId": 15,
                            "meta_options": {
                                "configs": {
                                    "options": [],
                                    "multiple": true,
                                    "datasource": "/api/financements?filter_type=source",
                                    "depends_on": "natures_financement",
                                    "filter_type": "source",
                                    "load_dynamic": true
                                },
                                "conditions": {
                                    "disable": false,
                                    "visible": true,
                                    "conditions": [
                                        {
                                            "field": "natures_financement",
                                            "operator": "not_empty"
                                        }
                                    ]
                                },
                                "validations_rules": {
                                    "min": 0,
                                    "array": true,
                                    "required": false
                                }
                            },
                            "champ_standard": false,
                            "startWithNewLine": null
                        }
                    ]
                }
            ]
        }
    ]
}
    */
}
