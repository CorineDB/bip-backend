<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\IdeeProjetRepositoryInterface;
use App\Services\Contracts\IdeeProjetServiceInterface;
use App\Http\Resources\IdeeProjetResource;
use Illuminate\Support\Facades\DB;
use App\Models\IdeeProjet;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Services\Traits\ProjetRelationsTrait;

class IdeeProjetService extends BaseService implements IdeeProjetServiceInterface
{
    use ProjetRelationsTrait;

    protected DocumentRepositoryInterface $documentRepository;

    public function __construct(IdeeProjetRepositoryInterface $repository, DocumentRepositoryInterface $documentRepository)
    {
        parent::__construct($repository);

        $this->documentRepository = $documentRepository;
    }

    protected function getResourceClass(): string
    {
        return IdeeProjetResource::class;
    }

    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $champsData = $data['champs'] ?? [];
            $relations = $this->extractRelationsFromChamps($champsData);

            // Créer ou récupérer l'idée de projet
            $idee = $this->getOrCreateIdeeProjet($data);

            // Remplir les attributs de base
            $this->fillIdeeFromChamps($idee, $champsData);

            $idee->update($champsData);

            $idee->save();

            // Synchroniser les relations
            $this->syncAllRelations($idee, $relations);

            // Sauvegarder les champs dynamiques
            $this->saveDynamicFields($idee, $champsData);

            $idee->refresh();

            DB::commit();

            return (new $this->resourceClass($idee))
                ->additional(['message' => 'Idée de projet sauvegardée avec succès.'])
                ->response()
                ->setStatusCode(isset($data['id']) ? 200 : 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Extraire toutes les relations des données de champs
     */
    private function extractRelationsFromChamps(array $champsData): array
    {
        $relations = [];
        $relationKeys = [
            'cibles',
            'odds',
            'resultats_strategiques',
            'objectifs_strategiques',
            'orientations_strategiques',
            'departements',
            'communes',
            'arrondissements',
            'villages',
            'sources_financement'
        ];

        foreach ($relationKeys as $key) {
            if (array_key_exists($key, $champsData)) {
                $relations[$key] = $champsData[$key];
            }
        }

        return $relations;
    }

    /**
     * Obtenir ou créer une idée de projet
     */
    private function getOrCreateIdeeProjet(array $data): IdeeProjet
    {
        if (isset($data['id'])) {
            return $this->repository->findOrFail($data['id']);
        }

        $idee = $this->repository->getModel();

        // Initialiser ficheIdee avec la structure complète du formulaire dès la création
        if (empty($idee->ficheIdee)) {
            $idee->ficheIdee = $this->initializeFicheIdeeStructure();
        }

        return $idee;
    }

    /**
     * Remplir l'idée de projet avec les données des champs
     */
    private function fillIdeeFromChamps(IdeeProjet $idee, array $champsData): void
    {
        $fillableAttributes = $this->getFillableAttributesFromChamps($champsData);

        // Ajouter les valeurs par défaut pour les colonnes JSON obligatoires si elles ne sont pas définies
        $this->setDefaultJsonValues($fillableAttributes);

        $idee->fill($fillableAttributes);
    }

    /**
     * Définir les valeurs par défaut pour les colonnes JSON obligatoires
     */
    private function setDefaultJsonValues(array &$attributes): void
    {
        $requiredJsonColumns = [
            'ficheIdee' => [],
            'body_projet' => []
        ];

        foreach ($requiredJsonColumns as $column => $defaultValue) {
            if (!isset($attributes[$column])) {
                $attributes[$column] = $defaultValue;
            }
        }
    }

    /**
     * Extraire les attributs remplissables des champs
     */
    private function getFillableAttributesFromChamps(array $champsData): array
    {
        $attributes = [];

        $fillableKeys = [
            "cout_estimatif_projet",
            "sigle",
            "parties_prenantes",
            "objectifs_specifiques",
            "resultats_attendus",
            "cout_dollar_americain",
            "cout_euro",
            "cout_dollar_canadien",
            "risques_immediats",
            "sommaire",
            "objectif_general",
            "conclusions",
            "constats_majeurs",
            "description",
            "public_cible",
            "estimation_couts",
            "impact_environnement",
            "aspect_organisationnel",
            "description_extrants",
            "duree",
            "description_projet",
            "origine",
            "situation_desiree",
            "situation_actuelle",
            "contraintes",
            "echeancier",
            "fondement",
            "secteurId",
            "categorieId",
            "titre_projet"
        ];

        // Colonnes JSON qui nécessitent un traitement spécial (selon la migration)
        $jsonColumns = [
            'cout_estimatif_projet',
            'ficheIdee',
            'parties_prenantes',
            'objectifs_specifiques',
            'resultats_attendus',
            'body_projet'
        ];

        foreach ($fillableKeys as $key) {
            if (array_key_exists($key, $champsData)) {
                $value = $champsData[$key];
                // Traitement spécial pour les colonnes JSON
                if (in_array($key, $jsonColumns)) {
                    if ($key == "cout_estimatif_projet") {
                        if (!is_array($value)) {
                            $data = json_encode(["montant" => $value, "devise" => "FCFA"]);
                            $attributes[$key] = $data;
                        } else {
                            $attributes[$key] = $this->prepareJsonValue($value);
                        }
                    } else {
                        $attributes[$key] = $this->prepareJsonValue($value);
                    }
                } else {
                    $attributes[$key] = $this->sanitizeAttributeValue($value);
                }
            }
        }

        //dd($attributes);

        return $attributes;
    }

    /**
     * Préparer une valeur pour une colonne JSON
     */
    private function prepareJsonValue($value)
    {
        // Si c'est déjà un array, le retourner tel quel (Laravel le convertira en JSON)
        if (is_array($value)) {
            return $value;
        }

        // Si c'est une chaîne vide ou null
        if (empty($value) || $value === '' || $value === null) {
            return null;
        }

        // Si c'est une chaîne JSON valide
        if (is_string($value) && $this->isValidJsonString($value)) {
            return json_decode($value, true);
        }

        // Sinon, encapsuler dans un array seulement si c'est une vraie valeur
        if (is_string($value) && trim($value) !== '') {
            return [$value];
        }

        return null;
    }

    /**
     * Nettoyer une valeur d'attribut
     */
    private function sanitizeAttributeValue($value)
    {
        // Si c'est un array mais pas pour une colonne JSON, on doit le convertir
        if (is_array($value)) {
            // Cas spéciaux selon la validation
            return $this->convertArrayToString($value);
        }

        if (is_string($value)) {
            return trim($value) ?: null;
        }

        if (is_numeric($value)) {
            return $value;
        }

        return $value;
    }

    /**
     * Convertir un array en valeur appropriée pour les colonnes non-JSON
     */
    private function convertArrayToString($array)
    {
        if (empty($array)) {
            return null;
        }

        // Si c'est un array avec une seule valeur, prendre cette valeur
        if (count($array) === 1) {
            return reset($array);
        }

        // Si c'est un array avec des clés spécifiques (comme duree.duree), prendre la première valeur significative
        /*if (isset($array['duree'])) {
            return $array['duree'];
        }*/

        /*if (isset($array['montant'])) {
            return $array['montant'];
        }*/

        // Sinon, joindre avec une virgule
        return implode(', ', array_filter($array));
    }

    /**
     * Vérifier si une chaîne est du JSON valide
     */
    private function isValidJsonString(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Synchroniser toutes les relations many-to-many
     */
    private function syncAllRelations(IdeeProjet $idee, array $relations): void
    {
        $relationMappings = [
            'cibles' => 'cibles',
            'odds' => 'odds',
            'sources_financement' => 'financements'
        ];

        foreach ($relationMappings as $key => $relation) {
            if (isset($relations[$key])) {
                $idee->$relation()->sync($relations[$key]);
            }
        }

        // Synchroniser les composants programme
        $this->syncComposantProgrammeRelations($idee, $relations);

        // Synchroniser les lieux d'intervention
        $this->syncLieuxIntervention($idee, $relations);
    }

    /**
     * Synchroniser les relations avec les composants de programme
     */
    private function syncComposantProgrammeRelations(IdeeProjet $idee, array $relations): void
    {
        $composantRelations = [
            'orientations_strategiques',
            'resultats_strategiques',
            'objectifs_strategiques',
            'axes_pag',
            'actions_pag',
            'piliers_pag',
        ];

        /**
         *
         * "axes_pag" => 'composants_projet',
         * "actions_pag" => 'composants_projet',
         * "piliers_pag" => 'composants_projet',
         * "resultats_strategiques" => 'composants_projet',
         * "orientations_strategiques" => 'composants_projet'
         * "objectifs_strategiques" => 'composants_projet',
         * "resultats_strategiques" => 'composants_projet'
         */

        $allComposants = [];
        foreach ($composantRelations as $relation) {
            if (isset($relations[$relation])) {
                $allComposants = array_merge($allComposants, $relations[$relation]);
            }
        }

        if (!empty($allComposants)) {
            $idee->composants()->sync($allComposants);
        }
    }

    /**
     * Synchroniser les lieux d'intervention
     */
    private function syncLieuxIntervention(IdeeProjet $idee, array $relations): void
    {
        $idee->lieuxIntervention()->create([
            'departementId' => $relations["departements"][0],
            'communeId' => $relations["communes"][0],
            'arrondissementId' => $relations["arrondissements"][0],
            'villageId' => $relations["villages"][0]
        ]);

        /*$lieuxTypes = [
            'departements' => 'departement',
            'communes' => 'commune',
            'arrondissements' => 'arrondissement',
            'villages' => 'village'
        ];

        $lieuxData = [];
        foreach ($lieuxTypes as $key => $type) {
            if (isset($relations[$key])) {
                foreach ($relations[$key] as $lieuId) {
                    $lieuxData[] = [
                        'type_lieu' => $type,
                        'lieu_id' => $lieuId,
                        'niveau_administratif' => $type
                    ];
                }
            }
        }

        if (!empty($lieuxData)) {
            // Créer ou synchroniser les lieux d'intervention
            $this->createOrSyncLieuxIntervention($idee, $lieuxData);
        }*/
    }

    /**
     * Créer ou synchroniser les lieux d'intervention
     */
    /*private function createOrSyncLieuxIntervention(IdeeProjet $idee, array $lieuxData): void
    {
        $lieuxIds = $this->createLieuxInterventionFromGeoData($lieuxData);

        if (!empty($lieuxIds)) {
            $idee->lieuxIntervention()->sync($lieuxIds);
        }
    }*/

    /**
     * Sauvegarder les champs dynamiques
     */
    private function saveDynamicFields(IdeeProjet $idee, array $champsData): void
    {
        //$sanitizedData = $this->sanitizeChampData($champsData);

        $champsDefinitions = $this->documentRepository->getFicheIdee()->all_champs;

        // Indexer par attribut pour accès rapide
        $champsMap = $champsDefinitions->keyBy('attribut');

        $syncData = [];

        foreach ($champsData as $attribut => $valeur) {
            if (isset($champsMap[$attribut])) {
                $champ = $champsMap[$attribut];
                $syncData[$champ->id] = [
                    'valeur' => $valeur ?? null,
                    'commentaire' => null
                ];
            }
        }

        // Synchroniser tous les champs reçus
        if (!empty($syncData)) {
            $idee->champs()->sync($syncData);
        }

        // Enregistrer l'historique
        $this->logProjectModification($idee, 'champs_updated', [
            'nb_champs' => count($syncData)
        ]);
    }

    /**
     * Préparer les données de la fiche idée avec le formulaire et les valeurs
     */
    private function prepareFicheIdeeData(array $champsData, ?array $existingFicheIdee = null): array
    {
        // Récupérer la fiche idée (document formulaire)
        $ficheIdee = \App\Models\Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'fiche-idee');
        })
            ->where('type', 'formulaire')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$ficheIdee) {
            return [];
        }

        // Utiliser la structure existante si elle existe, sinon créer une nouvelle
        if ($existingFicheIdee && !empty($existingFicheIdee)) {
            $ficheIdeeStructure = $existingFicheIdee;
            $ficheIdeeStructure['date_remplissage'] = now()->toISOString();
        } else {
            // Structure de base avec les informations de la fiche
            $ficheIdeeStructure = [
                'document_id' => $ficheIdee->id,
                'document_nom' => $ficheIdee->nom ?? 'Fiche Idée de Projet',
                'document_version' => $ficheIdee->version ?? '1.0',
                'date_creation' => now()->toISOString(),
                'date_remplissage' => now()->toISOString(),
                'sections' => [],
                'champs_values' => [],
                'relations_values' => []
            ];
        }

        // Organiser les données par sections
        foreach ($ficheIdee->sections as $section) {
            $sectionData = [
                'id' => $section->id,
                'nom' => $section->nom,
                'ordre' => $section->ordre,
                'champs' => []
            ];

            foreach ($section->champs as $champ) {
                $champData = [
                    'id' => $champ->id,
                    'nom' => $champ->nom,
                    'label' => $champ->label,
                    'type_champ' => $champ->type_champ,
                    'attribut' => $champ->attribut,
                    'required' => $champ->meta_options['validations_rules']['required'] ?? false,
                    'ordre' => $champ->ordre,
                    'valeur' => null,
                    'valeur_attribut' => null,
                    'relations' => []
                ];

                // Chercher la valeur correspondante dans champsData
                foreach ($champsData as $champInput) {
                    if (isset($champInput['id']) && $champInput['id'] == $champ->id) {
                        // Valeur directe du champ
                        if (isset($champInput['valeur'])) {
                            $champData['valeur'] = $champInput['valeur'];
                        }

                        // Valeur par attribut
                        if (isset($champInput[$champ->attribut])) {
                            $champData['valeur_attribut'] = $champInput[$champ->attribut];
                        }

                        // Relations associées
                        $relationKeys = [
                            'cibles',
                            'odds',
                            'sources_financement',
                            'orientations_strategiques',
                            'objectifs_strategiques',
                            'resultats_strategiques',
                            'departements',
                            'communes',
                            'arrondissements',
                            'villages'
                        ];

                        foreach ($relationKeys as $key) {
                            if (isset($champInput[$key]) && is_array($champInput[$key])) {
                                $champData['relations'][$key] = $champInput[$key];
                                $ficheIdeeStructure['relations_values'][$key] = $champInput[$key];
                            }
                        }

                        break;
                    }
                }

                $sectionData['champs'][] = $champData;

                // Ajouter à l'index global des valeurs pour accès rapide
                $ficheIdeeStructure['champs_values'][$champ->id] = [
                    'attribut' => $champ->attribut,
                    'valeur' => $champData['valeur'],
                    'valeur_attribut' => $champData['valeur_attribut'],
                    'relations' => $champData['relations']
                ];
            }

            $ficheIdeeStructure['sections'][] = $sectionData;
        }

        // Ajouter les champs directs de la fiche (non organisés en sections)
        if ($ficheIdee->champs && $ficheIdee->champs->count() > 0) {
            $champsDirects = [
                'id' => 'champs_directs',
                'nom' => 'Champs Directs',
                'ordre' => 999,
                'champs' => []
            ];

            foreach ($ficheIdee->champs as $champ) {
                $champData = [
                    'id' => $champ->id,
                    'nom' => $champ->nom,
                    'label' => $champ->label,
                    'type_champ' => $champ->type_champ,
                    'attribut' => $champ->attribut,
                    'required' => $champ->meta_options['validations_rules']['required'] ?? false,
                    'ordre' => $champ->ordre,
                    'valeur' => null,
                    'valeur_attribut' => null,
                    'relations' => []
                ];

                // Chercher la valeur correspondante
                foreach ($champsData as $champInput) {
                    if (isset($champInput['id']) && $champInput['id'] == $champ->id) {
                        if (isset($champInput['valeur'])) {
                            $champData['valeur'] = $champInput['valeur'];
                        }
                        if (isset($champInput[$champ->attribut])) {
                            $champData['valeur_attribut'] = $champInput[$champ->attribut];
                        }
                        break;
                    }
                }

                $champsDirects['champs'][] = $champData;
                $ficheIdeeStructure['champs_values'][$champ->id] = [
                    'attribut' => $champ->attribut,
                    'valeur' => $champData['valeur'],
                    'valeur_attribut' => $champData['valeur_attribut'],
                    'relations' => $champData['relations']
                ];
            }

            if (!empty($champsDirects['champs'])) {
                $ficheIdeeStructure['sections'][] = $champsDirects;
            }
        }

        // Ajouter des métadonnées de complétion
        $ficheIdeeStructure['metadata'] = [
            'total_champs' => count($ficheIdeeStructure['champs_values']),
            'champs_remplis' => count(array_filter($ficheIdeeStructure['champs_values'], function ($item) {
                return !empty($item['valeur']) || !empty($item['valeur_attribut']);
            })),
            'taux_completion' => 0,
            'relations_count' => array_map('count', $ficheIdeeStructure['relations_values']),
            'last_updated' => now()->toISOString()
        ];

        $totalChamps = $ficheIdeeStructure['metadata']['total_champs'];
        $champsRemplis = $ficheIdeeStructure['metadata']['champs_remplis'];
        $ficheIdeeStructure['metadata']['taux_completion'] = $totalChamps > 0 ?
            round(($champsRemplis / $totalChamps) * 100, 2) : 0;

        return $ficheIdeeStructure;
    }

    /**
     * Initialiser la structure de base de ficheIdee pour une nouvelle idée
     */
    private function initializeFicheIdeeStructure(): array
    {
        // Récupérer la fiche idée (document formulaire)
        $ficheIdee = \App\Models\Document::whereHas('categorie', function ($query) {
            $query->where('slug', 'fiche-idee');
        })
            ->where('type', 'formulaire')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$ficheIdee) {
            return [];
        }

        // Structure de base avec les informations de la fiche
        $ficheIdeeStructure = [
            'document_id' => $ficheIdee->id,
            'document_nom' => $ficheIdee->nom ?? 'Fiche Idée de Projet',
            'document_version' => $ficheIdee->version ?? '1.0',
            'date_creation' => now()->toISOString(),
            'date_remplissage' => null,
            'sections' => [],
            'champs_values' => [],
            'relations_values' => []
        ];

        // Organiser les sections avec champs vides
        foreach ($ficheIdee->sections as $section) {
            $sectionData = [
                'id' => $section->id,
                'nom' => $section->nom,
                'ordre' => $section->ordre,
                'champs' => []
            ];

            foreach ($section->champs as $champ) {
                $champData = [
                    'id' => $champ->id,
                    'nom' => $champ->nom,
                    'label' => $champ->label,
                    'type_champ' => $champ->type_champ,
                    'attribut' => $champ->attribut,
                    'required' => $champ->meta_options['validations_rules']['required'] ?? false,
                    'ordre' => $champ->ordre,
                    'valeur' => null, // Vide à l'initialisation
                    'valeur_attribut' => null, // Vide à l'initialisation
                    'relations' => [] // Vide à l'initialisation
                ];

                $sectionData['champs'][] = $champData;

                // Ajouter à l'index global des valeurs (vides)
                $ficheIdeeStructure['champs_values'][$champ->id] = [
                    'attribut' => $champ->attribut,
                    'valeur' => null,
                    'valeur_attribut' => null,
                    'relations' => []
                ];
            }

            $ficheIdeeStructure['sections'][] = $sectionData;
        }

        // Ajouter les champs directs de la fiche (non organisés en sections)
        if ($ficheIdee->champs && $ficheIdee->champs->count() > 0) {
            $champsDirects = [
                'id' => 'champs_directs',
                'nom' => 'Champs Directs',
                'ordre' => 999,
                'champs' => []
            ];

            foreach ($ficheIdee->champs as $champ) {
                $champData = [
                    'id' => $champ->id,
                    'nom' => $champ->nom,
                    'label' => $champ->label,
                    'type_champ' => $champ->type_champ,
                    'attribut' => $champ->attribut,
                    'required' => $champ->meta_options['validations_rules']['required'] ?? false,
                    'ordre' => $champ->ordre,
                    'valeur' => null, // Vide à l'initialisation
                    'valeur_attribut' => null, // Vide à l'initialisation
                    'relations' => [] // Vide à l'initialisation
                ];

                $champsDirects['champs'][] = $champData;
                $ficheIdeeStructure['champs_values'][$champ->id] = [
                    'attribut' => $champ->attribut,
                    'valeur' => null,
                    'valeur_attribut' => null,
                    'relations' => []
                ];
            }

            if (!empty($champsDirects['champs'])) {
                $ficheIdeeStructure['sections'][] = $champsDirects;
            }
        }

        // Ajouter des métadonnées de complétion (vides à l'initialisation)
        $ficheIdeeStructure['metadata'] = [
            'total_champs' => count($ficheIdeeStructure['champs_values']),
            'champs_remplis' => 0,
            'taux_completion' => 0,
            'relations_count' => [],
            'created_at' => now()->toISOString(),
            'last_updated' => null
        ];

        return $ficheIdeeStructure;
    }

    /**
     * Récupérer les données du formulaire depuis ficheIdee pour mise à jour
     */
    public function getFormDataFromFicheIdee(IdeeProjet $idee): array
    {
        $ficheIdeeData = $idee->ficheIdee ?? [];

        if (empty($ficheIdeeData) || !isset($ficheIdeeData['champs_values'])) {
            return [];
        }

        $formData = [];

        // Reconstituer le format des champs pour la mise à jour
        foreach ($ficheIdeeData['champs_values'] as $champId => $champValue) {
            $champData = [
                'id' => $champId,
            ];

            // Ajouter la valeur si elle existe
            if (!empty($champValue['valeur'])) {
                $champData['valeur'] = $champValue['valeur'];
            }

            // Ajouter la valeur d'attribut si elle existe
            if (!empty($champValue['valeur_attribut']) && !empty($champValue['attribut'])) {
                $champData[$champValue['attribut']] = $champValue['valeur_attribut'];
            }

            // Ajouter les relations si elles existent
            if (!empty($champValue['relations']) && is_array($champValue['relations'])) {
                foreach ($champValue['relations'] as $relationKey => $relationValue) {
                    $champData[$relationKey] = $relationValue;
                }
            }

            $formData[] = $champData;
        }

        // Ajouter les relations globales
        if (isset($ficheIdeeData['relations_values']) && is_array($ficheIdeeData['relations_values'])) {
            foreach ($ficheIdeeData['relations_values'] as $relationKey => $relationValue) {
                // Vérifier si cette relation n'est pas déjà dans un champ spécifique
                $found = false;
                foreach ($formData as &$champData) {
                    if (isset($champData[$relationKey])) {
                        $found = true;
                        break;
                    }
                }

                // Si la relation n'est pas associée à un champ spécifique, l'ajouter comme champ global
                if (!$found && !empty($relationValue)) {
                    $formData[] = [
                        'id' => 'global_' . $relationKey,
                        $relationKey => $relationValue
                    ];
                }
            }
        }

        return $formData;
    }

    /**
     * Obtenir les métadonnées de complétion depuis ficheIdee
     */
    public function getCompletionMetadata(IdeeProjet $idee): array
    {
        $ficheIdeeData = $idee->ficheIdee ?? [];

        if (empty($ficheIdeeData) || !isset($ficheIdeeData['metadata'])) {
            return [
                'total_champs' => 0,
                'champs_remplis' => 0,
                'taux_completion' => 0,
                'relations_count' => [],
                'last_updated' => null
            ];
        }

        return $ficheIdeeData['metadata'];
    }

    /**
     * Méthode de mise à jour améliorée
     */
    public function update($id, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $champsData = $data['champs'] ?? [];
            $relations = $this->extractRelationsFromChamps($champsData);

            // Créer ou récupérer l'idée de projet
            $idee = $this->getOrCreateIdeeProjet($data);

            // Remplir les attributs de base
            $this->fillIdeeFromChamps($idee, $champsData);

            $idee->update($champsData);

            // Sauvegarder les champs dynamiques
            $this->saveDynamicFields($idee, $champsData);

            $idee->save();


            $idee->refresh();

            DB::commit();

            return (new $this->resourceClass($idee))
                ->additional(['message' => 'Idée de projet sauvegardée avec succès.'])
                ->response()
                ->setStatusCode(isset($data['id']) ? 200 : 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }
}
