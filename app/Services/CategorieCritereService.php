<?php

namespace App\Services;

use App\Helpers\SlugHelper;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CategorieCritereRepositoryInterface;
use App\Services\Contracts\CategorieCritereServiceInterface;
use App\Http\Resources\CategorieCritereResource;
use App\Http\Resources\ChecklistMesuresAdaptationResource;
use App\Http\Resources\ChecklistMesuresAdaptationSecteurResource;
use App\Http\Resources\SecteurResource;
use App\Models\Secteur;
use App\Models\Critere;
use App\Models\Notation;
use App\Models\Dossier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorieCritereService extends BaseService implements CategorieCritereServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        CategorieCritereRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CategorieCritereResource::class;
    }

    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $criteres = $data['criteres'] ?? [];
            $notationsCategorie = $data['notations'] ?? [];
            unset($data['criteres'], $data['notations']);

            $categorieCritere = $this->repository->create($data);

            if (!empty($notationsCategorie)) {
                foreach ($notationsCategorie as $notationData) {
                    $notationData['categorie_critere_id'] = $categorieCritere->id;
                    $notationData['critere_id'] = null;

                    Notation::create($notationData);
                }
            }

            if (!empty($criteres)) {
                foreach ($criteres as $critereData) {
                    $critereData['categorie_critere_id'] = $categorieCritere->id;

                    $notations = $critereData['notations'] ?? [];
                    unset($critereData['notations']);

                    $critere = Critere::create($critereData);

                    if (!empty($notations)) {
                        foreach ($notations as $notationData) {
                            $notationData['critere_id'] = $critere->id;
                            $notationData['categorie_critere_id'] = $categorieCritere->id;

                            Notation::create($notationData);
                        }
                    }
                }
            }

            DB::commit();

            return (new $this->resourceClass($categorieCritere->load(['criteres.notations', 'notations', 'fichiers'])))
                ->additional(['message' => 'Catégorie critère créée avec succès.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function update(int|string $id, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $categorieCritere = $this->repository->findOrFail($id);

            $criteres = $data['criteres'] ?? [];
            $notationsCategorie = $data['notations'] ?? [];
            unset($data['criteres'], $data['notations']);

            if (!empty($data)) {
                $categorieCritere->fill($data);

                $categorieCritere->save();
            }

            if (!empty($notationsCategorie)) {
                foreach ($notationsCategorie as $notationData) {
                    if (isset($notationData['id']) && $notationData['id']) {
                        $notation = Notation::findOrFail($notationData['id']);
                        $notationIdsFromRequest[] = $notation->id;
                        $notation->update($notationData);
                    } else {
                        $notationData['categorie_critere_id'] = $categorieCritere->id;
                        $notationData['critere_id'] = null;
                        $notation = Notation::create($notationData);
                        $notationIdsFromRequest[] = $notation->id;
                    }
                }
                // Supprimer les notations de catégorie non présentes dans la requête
                Notation::where('categorie_critere_id', $categorieCritere->id)
                    ->whereNull('critere_id')
                    ->whereNotIn('id', $notationIdsFromRequest)
                    ->forceDelete();
            }

            // ======= 3. GESTION DES CRITERES =======

            // 3. Mise à jour ou ajout des critères et leurs notations
            if (!empty($criteres)) {
                $critereIdsFromRequest = [];
                foreach ($criteres as $critereData) {
                    $notations = $critereData['notations'] ?? [];
                    unset($critereData['notations']);

                    if (isset($critereData['id']) && $critereData['id']) {
                        $critere = Critere::findOrFail($critereData['id']);
                        $critere->update($critereData);
                        $critereIdsFromRequest[] = $critere->id;
                    } else {
                        $critereData['categorie_critere_id'] = $categorieCritere->id;
                        $critere = Critere::create($critereData);
                        $critereIdsFromRequest[] = $critere->id;
                    }

                    // ======= 4. GESTION DES NOTATIONS DE CRITERE =======
                    if (!empty($notations)) {
                        $notationIdsCritere = [];
                        foreach ($notations as $notationData) {
                            if (isset($notationData['id']) && $notationData['id']) {
                                $notation = Notation::findOrFail($notationData['id']);
                                $notation->update($notationData);
                                $notationIdsCritere[] = $notation->id;
                            } else {
                                $notationData['critere_id'] = $critere->id;
                                $notationData['categorie_critere_id'] = $categorieCritere->id;
                                $notation = Notation::create($notationData);
                                $notationIdsCritere[] = $notation->id;
                            }
                        }

                        // Supprimer les anciennes notations du critère qui ne sont plus présentes
                        Notation::where('critere_id', $critere->id)
                            ->whereNotIn('id', $notationIdsCritere)
                            ->forceDelete();
                    }
                }
                // Supprimer les anciens critères (et leurs notations) qui ne figurent plus dans la requête
                $criteresToDelete = $categorieCritere->criteres()->whereNotIn('id', $critereIdsFromRequest)->get();
                foreach ($criteresToDelete as $critere) {
                    // Supprimer les notations liées à ce critère
                    Notation::where('critere_id', $critere->id)->forceDelete();
                    $critere->forceDelete();
                }
            }

            $categorieCritere->refresh();

            DB::commit();

            return (new $this->resourceClass($categorieCritere->load(['criteres.notations', 'notations', 'fichiers'])))
                ->additional(['message' => 'Catégorie critère mise à jour avec succès.'])
                ->response();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Get the grille evaluation preliminaire des impacts climatique
     */
    public function getGrilleEvaluationPreliminaire(): JsonResponse
    {
        try {
            $grille = $this->repository->findByAttribute('slug', 'evaluation-preliminaire-multi-projet-impact-climatique');

            if (!$grille) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grille d\'évaluation préliminaire non trouvée.',
                ], 404);
            }

            return (new $this->resourceClass($grille->load(['criteres.notations', 'notations', 'fichiers'])))
                ->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Update the grille evaluation preliminaire des impacts climatique
     */
    public function updateGrilleEvaluationPreliminaire(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $grille = $this->repository->findByAttribute('slug', 'evaluation-preliminaire-multi-projet-impact-climatique');

            $data["slug"] = 'evaluation-preliminaire-multi-projet-impact-climatique';
            if (!$grille) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grille d\'évaluation préliminaire non trouvée.',
                ], 404);
            }

            // Traiter les documents référentiels s'il y en a
            if (isset($data['documents_referentiel']) && !empty($data['documents_referentiel'])) {
                $nomsFilesSoumis = [];

                // Créer ou récupérer la structure de dossiers
                $dossierCanevas = $this->getOrCreateCanvasFolderStructure('appreciation');

                foreach ($data['documents_referentiel'] as $file) {
                    $nomOriginal = $file->getClientOriginalName();
                    $nomsFilesSoumis[] = $nomOriginal;

                    // Vérifier si un fichier avec ce nom existe déjà
                    $fichierExistant = $grille->fichiers()
                        ->where('nom_original', $nomOriginal)
                        ->where('categorie', 'guide-referentiel-appreciation')
                        ->first();

                    // Stocker le nouveau fichier dans le dossier structuré sur disque local avec sous-dossiers publics
                    $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $file->hashName();
                    
                    // Créer le chemin basé sur la structure de dossiers en base de données
                    $cheminStockage = $dossierCanevas ? 
                        'public/' . $dossierCanevas->full_path :
                        'public/Canevas, guides et outils/Phase d\'Identification/Analyse d\'idee de projet/Analyse preliminaire de l\'impact climatique';
                        
                    $path = $file->storeAs($cheminStockage, $nomStockage, 'local');

                    if ($fichierExistant) {
                        // Mettre à jour le fichier existant avec détails complets
                        $fichierExistant->update([
                            'nom_stockage' => $nomStockage,
                            'chemin' => $path,
                            'extension' => $file->getClientOriginalExtension(),
                            'mime_type' => $file->getMimeType(),
                            'taille' => $file->getSize(),
                            'hash_md5' => md5_file($file->getRealPath()),
                            'uploaded_by' => auth()->id(),
                            'is_public' => true,
                            'is_active' => true,
                            'metadata' => array_merge($fichierExistant->metadata ?? [], [
                                'last_updated' => now()->toISOString(),
                                'updated_by' => auth()->id(),
                                'version_updated' => ($fichierExistant->metadata['version'] ?? 0) + 1
                            ])
                        ]);
                    } else {
                        // Créer un nouveau fichier avec détails complets
                        $grille->fichiers()->create([
                            'nom_original' => $nomOriginal,
                            'nom_stockage' => $nomStockage,
                            'chemin' => $path,
                            'extension' => $file->getClientOriginalExtension(),
                            'mime_type' => $file->getMimeType(),
                            'taille' => $file->getSize(),
                            'hash_md5' => md5_file($file->getRealPath()),
                            'description' => 'Guide référentiel pour l\'appreciation de l\'impact climatique des idées de projet.',
                            'commentaire' => 'Document de référence pour l\'analyse climatique',
                            'categorie' => 'guide-referentiel-appreciation',
                            'dossier_id' => $dossierCanevas?->id,
                            'uploaded_by' => auth()->id(),
                            'is_public' => true,
                            'is_active' => true,
                            'metadata' => [
                                'type_document' => 'guide-referentiel-appreciation',
                                'grille_id' => $grille->id,
                                'uploaded_context' => 'evaluation-preliminaire-impact-climatique',
                                'soumis_par' => auth()->id(),
                                'soumis_le' => now()->toISOString(),
                                'dossier_public' => $dossierCanevas ? $dossierCanevas->full_path : 'Canevas, guides et outils/Phase d\'Identification/Analyse d\'idee de projet/Analyse preliminaire de l\'impact climatique'
                            ]
                        ]);
                    }
                }

                // Supprimer les fichiers qui ne sont plus soumis
                $grille->fichiers()
                    ->where('categorie', 'guide-referentiel-appreciation')
                    ->whereNotIn('nom_original', $nomsFilesSoumis)
                    ->forceDelete();
            }

            // Enlever les fichiers des données avant mise à jour
            unset($data['documents_referentiel']);

            // Mettre à jour la grille
            $result = $this->update($grille->id, $data);

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Get the grille analyse multi-criteres for an idee de projet
     */
    public function getGrilleAnalyseMultiCriteres(): JsonResponse
    {
        try {
            $grille = $this->repository->findByAttribute('slug', 'grille-analyse-multi-critere');

            if (!$grille) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grille d\'analyse multi-critères non trouvée.',
                ], 404);
            }

            return (new $this->resourceClass($grille))
                ->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Get the grille analyse multi-criteres with evaluations for an idee de projet
     */
    public function getGrilleAnalyseMultiCriteresAvecEvaluations(int $ideeProjetId): JsonResponse
    {
        try {
            $grille = $this->repository->findByAttribute('slug', 'grille-analyse-multi-critere');

            if (!$grille) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grille d\'analyse multi-critères non trouvée.',
                ], 404);
            }

            // Load the grille with criteres, notations and evaluations for the specific idee projet
            $grille->load([
                'criteres.notations',
                'criteres.evaluations' => function ($query) use ($ideeProjetId) {
                    $query->where('projetable_type', 'App\\Models\\IdeeProjet')
                        ->where('projetable_id', $ideeProjetId);
                },
                'notations'
            ]);

            return (new $this->resourceClass($grille))
                ->additional(['idee_projet_id' => $ideeProjetId])
                ->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Update the grille analyse multi-criteres
     */
    public function updateGrilleAnalyseMultiCriteres(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $grille = $this->repository->findByAttribute('slug', 'grille-analyse-multi-critere');

            $data["slug"] = 'grille-analyse-multi-critere';
            if (!$grille) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grille d\'analyse multi-critères non trouvée.',
                ], 404);
            }

            // Traiter les documents référentiels s'il y en a
            if (isset($data['documents_referentiel']) && !empty($data['documents_referentiel'])) {
                $nomsFilesSoumis = [];

                // Créer ou récupérer la structure de dossiers pour AMC
                $dossierCanevas = $this->getOrCreateCanvasFolderStructure('amc');

                foreach ($data['documents_referentiel'] as $file) {
                    $nomOriginal = $file->getClientOriginalName();
                    $nomsFilesSoumis[] = $nomOriginal;

                    // Vérifier si un fichier avec ce nom existe déjà
                    $fichierExistant = $grille->fichiers()
                        ->where('nom_original', $nomOriginal)
                        ->where('categorie', 'guide-referentiel-amc')
                        ->first();

                    // Stocker le nouveau fichier dans le dossier structuré sur disque local avec sous-dossiers publics
                    $nomStockage = now()->format('Y_m_d_His') . '_' . uniqid() . '_' . $file->hashName();
                    
                    // Créer le chemin basé sur la structure de dossiers en base de données
                    $cheminStockage = $dossierCanevas ? 
                        'public/' . $dossierCanevas->full_path :
                        'public/Canevas, guides et outils/Phase d\'Identification/Analyse d\'idee de projet/Analyse multicritere';
                        
                    $path = $file->storeAs($cheminStockage, $nomStockage, 'local');

                    if ($fichierExistant) {
                        // Mettre à jour le fichier existant avec détails complets
                        $fichierExistant->update([
                            'nom_stockage' => $nomStockage,
                            'chemin' => $path,
                            'extension' => $file->getClientOriginalExtension(),
                            'mime_type' => $file->getMimeType(),
                            'taille' => $file->getSize(),
                            'hash_md5' => md5_file($file->getRealPath()),
                            'uploaded_by' => auth()->id(),
                            'is_public' => true,
                            'is_active' => true,
                            'dossier_id' => $dossierCanevas?->id,
                            'metadata' => array_merge($fichierExistant->metadata ?? [], [
                                'last_updated' => now()->toISOString(),
                                'updated_by' => auth()->id(),
                                'version_updated' => ($fichierExistant->metadata['version'] ?? 0) + 1
                            ])
                        ]);
                    } else {
                        // Créer un nouveau fichier avec détails complets
                        $grille->fichiers()->create([
                            'nom_original' => $nomOriginal,
                            'nom_stockage' => $nomStockage,
                            'chemin' => $path,
                            'extension' => $file->getClientOriginalExtension(),
                            'mime_type' => $file->getMimeType(),
                            'taille' => $file->getSize(),
                            'hash_md5' => md5_file($file->getRealPath()),
                            'description' => 'Guide référentiel pour l\'analyse multicritère',
                            'commentaire' => 'Document de référence pour l\'analyse multicritère (AMC)',
                            'categorie' => 'guide-referentiel-amc',
                            'dossier_id' => $dossierCanevas?->id,
                            'uploaded_by' => auth()->id(),
                            'is_public' => true,
                            'is_active' => true,
                            'metadata' => [
                                'type_document' => 'guide-referentiel-amc',
                                'grille_id' => $grille->id,
                                'uploaded_context' => 'analyse-multicritere',
                                'soumis_par' => auth()->id(),
                                'soumis_le' => now()->toISOString(),
                                'dossier_public' => $dossierCanevas ? $dossierCanevas->full_path : 'Canevas, guides et outils/Phase d\'Identification/Analyse d\'idee de projet/Analyse multicritere'
                            ]
                        ]);
                    }
                }

                // Supprimer les fichiers qui ne sont plus soumis
                $grille->fichiers()
                    ->where('categorie', 'guide-referentiel-amc')
                    ->whereNotIn('nom_original', $nomsFilesSoumis)
                    ->forceDelete();
            }

            // Enlever les fichiers des données avant mise à jour
            unset($data['documents_referentiel']);

            // Mettre à jour la grille
            $result = $this->update($grille->id, $data);

            DB::commit();
            return $result;
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer la checklist des mesures d'adaptation pour projets à haut risque
     */
    public function getChecklistMesuresAdaptation(): JsonResponse
    {
        try {
            $checklistCategorie = $this->repository->findByAttribute('slug', 'checklist-mesures-adaptation-haut-risque');

            if (!$checklistCategorie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checklist des mesures d\'adaptation non trouvée. Veuillez exécuter les seeders.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Checklist des mesures d\'adaptation récupérée avec succès.',
                'data' => new ChecklistMesuresAdaptationResource($checklistCategorie->load(['criteres.notations.secteur', 'fichiers']))
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer la checklist des mesures d'adaptation pour projets à haut risque
     */
    public function getChecklistMesuresAdaptationSecteur($idSecteur): JsonResponse
    {
        try {
            // Vérifier que le secteur existe et n'est pas un grand secteur
            $secteur = Secteur::whereIn('type', ['secteur', 'sous-secteur'])->findOrFail($idSecteur);

            // Déterminer l'ID du secteur à utiliser pour le filtrage
            $secteurIdPourFiltrage = $idSecteur;

            // Si c'est un sous-secteur, récupérer son secteur parent pour le filtrage
            if ($secteur->type->value === 'sous-secteur') {
                $secteurParent = $secteur->parent;
                if ($secteurParent) {
                    $secteurIdPourFiltrage = $secteurParent->id;
                }
            }

            $checklistCategorie = $this->repository->findByAttribute('slug', 'checklist-mesures-adaptation-haut-risque');

            if (!$checklistCategorie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checklist des mesures d\'adaptation non trouvée. Veuillez exécuter les seeders.',
                ], 404);
            }

            // Charger la checklist avec les critères et notations filtrés par secteur
            $checklistCategorie->load([
                'criteres' => function($query) use ($secteurIdPourFiltrage) {
                    $query->withNotationsDuSecteur($secteurIdPourFiltrage);
                },
                'fichiers'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checklist des mesures d\'adaptation récupérée avec succès pour le secteur "' . $secteur->nom . '".',
                'data' => new ChecklistMesuresAdaptationSecteurResource($checklistCategorie),
                'secteur' => new SecteurResource($secteur)
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Soumettre la checklist des mesures d'adaptation pour un projet
     */
    public function soumettreChecklistMesuresAdaptation(int $projetId, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Vérifier que le projet existe
            $projet = \App\Models\Projet::find($projetId);
            if (!$projet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projet non trouvé.',
                ], 404);
            }

            // Récupérer la catégorie de critère pour la checklist
            $checklistCategorie = $this->repository->findByAttribute('slug', 'checklist-mesures-adaptation-haut-risque');
            if (!$checklistCategorie) {
                return response()->json([
                    'success' => false,
                    'message' => 'Checklist des mesures d\'adaptation non trouvée. Veuillez exécuter les seeders.',
                ], 404);
            }

            // Valider les données requises
            if (!isset($data['reponses']) || !is_array($data['reponses'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Les réponses à la checklist sont requises.',
                ], 422);
            }

            // Créer ou mettre à jour l'évaluation de la checklist
            $evaluation = \App\Models\Evaluation::updateOrCreate([
                'projet_id' => $projetId,
                'categorie_critere_id' => $checklistCategorie->id,
                'type_evaluation' => 'checklist_mesures_adaptation'
            ], [
                'statut' => 'en_cours',
                'date_debut' => now(),
                'evaluateur_id' => auth()->id()
            ]);

            $scoreTotal = 0;
            $scoreMaximal = 0;
            $criteresEvalues = 0;

            // Traiter chaque réponse
            foreach ($data['reponses'] as $critereLId => $reponseData) {
                $critere = \App\Models\Critere::find($critereLId);
                if (!$critere || $critere->categorie_critere_id !== $checklistCategorie->id) {
                    continue;
                }

                $notation = \App\Models\Notation::find($reponseData['notation_id'] ?? null);
                if (!$notation || $notation->critere_id !== $critere->id) {
                    continue;
                }

                // Enregistrer la réponse
                \App\Models\EvaluationCritere::updateOrCreate([
                    'evaluation_id' => $evaluation->id,
                    'critere_id' => $critere->id
                ], [
                    'notation_id' => $notation->id,
                    'commentaire' => $reponseData['commentaire'] ?? null,
                    'score' => $notation->valeur
                ]);

                $scoreTotal += $notation->valeur;
                $scoreMaximal += 3; // Score maximum par critère
                $criteresEvalues++;
            }

            // Calculer le score final et déterminer le statut
            $scorePourcentage = $scoreMaximal > 0 ? ($scoreTotal / $scoreMaximal) * 100 : 0;

            $statutFinal = 'non_conforme';
            if ($scorePourcentage >= 80) {
                $statutFinal = 'conforme';
            } elseif ($scorePourcentage >= 60) {
                $statutFinal = 'partiellement_conforme';
            }

            // Mettre à jour l'évaluation avec les résultats
            $evaluation->update([
                'score_total' => $scoreTotal,
                'score_maximal' => $scoreMaximal,
                'score_pourcentage' => $scorePourcentage,
                'statut' => 'termine',
                'date_fin' => now(),
                'metadata' => [
                    'commentaire_global' => $data['commentaire_global'] ?? null,
                    'statut_checklist' => $statutFinal,
                    'nombre_criteres_evalues' => $criteresEvalues
                ]
            ]);

            // Mettre à jour les métadonnées du projet
            $metadata = $projet->metadata ?? [];
            $metadata['checklist_mesures_adaptation'] = [
                'evaluation_id' => $evaluation->id,
                'score_total' => $scoreTotal,
                'score_pourcentage' => $scorePourcentage,
                'statut' => $statutFinal,
                'date_validation' => now(),
                'valide_par' => auth()->id()
            ];
            $projet->update(['metadata' => $metadata]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checklist des mesures d\'adaptation soumise avec succès.',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'score_total' => $scoreTotal,
                    'score_maximal' => $scoreMaximal,
                    'score_pourcentage' => round($scorePourcentage, 2),
                    'statut_checklist' => $statutFinal,
                    'criteres_evalues' => $criteresEvalues
                ]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Créer ou mettre à jour la checklist des mesures d'adaptation
     */
    public function createOrUpdateChecklistMesuresAdaptation(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Rechercher ou créer la catégorie checklist
            $checklistCategorie = $this->repository->getModel()->firstOrCreate([
                'slug' => 'checklist-mesures-adaptation-haut-risque'
            ], [
                'type' => "checklist",
                'slug' => 'checklist-mesures-adaptation-haut-risque',
                'is_mandatory' => true
            ]);

            // Mettre à jour les informations de base si c'est une mise à jour
            if (isset($data['type'])) {
                $checklistCategorie->update([
                    'type' => "checklist",
                    'is_mandatory' => true
                ]);
            }

            // Traiter les critères avec leur structure secteurs/notations (mise à jour incrémentale)
            $this->processCriteresWithSecteurs($checklistCategorie, $data['criteres']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Checklist des mesures d\'adaptation créée/mise à jour avec succès.',
                'data' => new ChecklistMesuresAdaptationResource(
                    $checklistCategorie->fresh(['criteres.notations.secteur'])
                )
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Nettoyer les critères et notations existants
     */
    private function cleanupExistingCriteres($checklistCategorie): void
    {
        // Supprimer toutes les notations liées aux critères de cette catégorie
        Notation::whereHas('critere', function ($query) use ($checklistCategorie) {
            $query->where('categorie_critere_id', $checklistCategorie->id);
        })->forceDelete();

        // Supprimer les notations directes de la catégorie (si il y en a)
        Notation::where('categorie_critere_id', $checklistCategorie->id)
            ->whereNull('critere_id')
            ->forceDelete();

        // Supprimer tous les critères de cette catégorie
        Critere::where('categorie_critere_id', $checklistCategorie->id)->forceDelete();
    }

    /**
     * Traiter les critères avec leur structure secteurs/notations (mise à jour incrémentale)
     */
    private function processCriteresWithSecteurs($checklistCategorie, array $criteres): void
    {
        $criteresTraites = [];

        foreach ($criteres as $critereData) {
            if (isset($critereData["id"]) && $critereData["id"]) {
                // Mise à jour d'un critère existant
                $critere = Critere::find($critereData["id"]);

                if (!$critere || $critere->categorie_critere_id !== $checklistCategorie->id) {
                    continue; // Critère non trouvé ou pas dans la bonne catégorie
                }

                $critere->update([
                    'intitule' => $critereData['intitule'],
                    'ponderation' => $critereData['ponderation'],
                    'commentaire' => $critereData['commentaire'] ?? null,
                    'is_mandatory' => $critereData['is_mandatory'] ?? true
                ]);

                $criteresTraites[] = $critere->id;
            } else {
                // Création d'un nouveau critère
                $critere = Critere::create([
                    'intitule' => $critereData['intitule'],
                    'categorie_critere_id' => $checklistCategorie->id,
                    'ponderation' => $critereData['ponderation'],
                    'commentaire' => $critereData['commentaire'] ?? null,
                    'is_mandatory' => $critereData['is_mandatory'] ?? true
                ]);

                $criteresTraites[] = $critere->id;
            }

            // Traiter les secteurs et leurs mesures
            $mesuresTraitees = [];

            foreach ($critereData['secteurs'] as $secteurData) {
                // Vérifier que secteur_id est fourni
                if (!isset($secteurData['secteur_id'])) {
                    continue;
                }

                $secteur = Secteur::find($secteurData['secteur_id']);
                if (!$secteur) {
                    continue; // Secteur non trouvé, ignorer
                }

                // Traiter les mesures de ce secteur pour ce critère
                if (isset($secteurData['mesures']) && is_array($secteurData['mesures'])) {
                    foreach ($secteurData['mesures'] as $mesureData) {
                        // Gestion des mesures existantes ou nouvelles
                        if (isset($mesureData['id']) && $mesureData['id']) {
                            $mesure = Notation::find($mesureData['id']);

                            if ($mesure && $mesure->critere_id === $critere->id) {
                                // Mise à jour d'une mesure existante du bon critère
                                $mesure->update([
                                    'libelle' => $mesureData['libelle'],
                                    'valeur' => $mesureData['valeur'] ?? SlugHelper::generate($mesureData['libelle']),
                                    'commentaire' => $mesureData['commentaire'] ?? $mesureData['description'] ?? null,
                                ]);
                                $mesuresTraitees[] = $mesure->id;
                            } else if ($mesure && $mesure->critere_id !== $critere->id) {
                                // Mesure appartient à un autre critère, on lui change de critère et on met à jour
                                $mesure->update([
                                    'critere_id' => $critere->id,
                                    'secteur_id' => $secteur->id,
                                    'libelle' => $mesureData['libelle'],
                                    'valeur' => $mesureData['valeur'] ?? SlugHelper::generate($mesureData['libelle']),
                                    'commentaire' => $mesureData['commentaire'] ?? $mesureData['description'] ?? null
                                ]);
                                $mesuresTraitees[] = $mesure->id;
                            } else {
                                // Mesure introuvable, créer une nouvelle
                                $nouvelleMesure = Notation::create([
                                    'categorie_critere_id' => $checklistCategorie->id,
                                    'critere_id' => $critere->id,
                                    'secteur_id' => $secteur->id,
                                    'libelle' => $mesureData['libelle'],
                                    'valeur' => $mesureData['valeur'] ?? SlugHelper::generate($mesureData['libelle']),
                                    'commentaire' => $mesureData['commentaire'] ?? $mesureData['description'] ?? null
                                ]);
                                $mesuresTraitees[] = $nouvelleMesure->id;
                            }
                        } else {
                            // Création d'une nouvelle mesure
                            $nouvelleMesure = Notation::create([
                                'categorie_critere_id' => $checklistCategorie->id,
                                'critere_id' => $critere->id,
                                'secteur_id' => $secteur->id,
                                'libelle' => $mesureData['libelle'],
                                'valeur' => $mesureData['valeur'] ?? SlugHelper::generate($mesureData['libelle']),
                                'commentaire' => $mesureData['commentaire'] ?? $mesureData['description'] ?? null
                            ]);
                            $mesuresTraitees[] = $nouvelleMesure->id;
                        }
                    }
                }
            }

            // Supprimer les mesures non traitées pour ce critère (celles qui ont été retirées)
            if (!empty($mesuresTraitees)) {
                Notation::where('critere_id', $critere->id)
                    ->whereNotIn('id', $mesuresTraitees)
                    ->forceDelete();
            }
        }

        // Supprimer les critères non traités pour cette catégorie (ceux qui ont été retirés)
        if (!empty($criteresTraites)) {
            $criteresASupprimer = Critere::where('categorie_critere_id', $checklistCategorie->id)
                ->whereNotIn('id', $criteresTraites)
                ->pluck('id');

            if ($criteresASupprimer->isNotEmpty()) {
                // Supprimer d'abord les notations liées aux critères à supprimer
                Notation::whereIn('critere_id', $criteresASupprimer)->forceDelete();

                // Puis supprimer les critères
                Critere::whereIn('id', $criteresASupprimer)->forceDelete();
            }
        }
    }



    /**
     * Récupérer ou créer un secteur et retourner son ID
     */
    private function getOrCreateSecteur(string $nomSecteur): int
    {
        $slug = Str::slug($nomSecteur);

        $secteur = Secteur::firstOrCreate([
            'slug' => $slug,
            'type' => 'secteur'
        ], [
            'nom' => $nomSecteur,
            'slug' => $slug,
            'description' => "Secteur {$nomSecteur} pour checklist d'adaptation"
        ]);

        return $secteur->id;
    }

    /**
     * Créer ou récupérer la structure de dossiers hiérarchique pour les canevas
     */
    private function getOrCreateCanvasFolderStructure(string $type = 'appreciation'): ?Dossier
    {
        try {
            // 1. Dossier racine : "Canevas, guides et outils"
            $dossierRacine = Dossier::firstOrCreate([
                'nom' => 'Canevas, guides et outils',
                'parent_id' => null
            ], [
                'nom' => 'Canevas, guides et outils',
                'description' => 'Dossier principal contenant tous les canevas, guides et outils',
                'parent_id' => null,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#2563EB',
                'icone' => 'folder-open'
                // profondeur et path sont calculés automatiquement par le modèle
            ]);

            // 2. Sous-dossier : "Phase d'Identification"
            $dossierPhase = Dossier::firstOrCreate([
                'nom' => "Phase d'Identification",
                'parent_id' => $dossierRacine->id
            ], [
                'nom' => "Phase d'Identification",
                'description' => 'Documents de la phase d\'identification des projets',
                'parent_id' => $dossierRacine->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#059669',
                'icone' => 'document-text'
                // profondeur et path sont calculés automatiquement par le modèle
            ]);

            // 3. Sous-dossier : "Analyse d'idee de projet"
            $dossierAnalyse = Dossier::firstOrCreate([
                'nom' => "Analyse d'idee de projet",
                'parent_id' => $dossierPhase->id
            ], [
                'nom' => "Analyse d'idee de projet",
                'description' => 'Outils et guides pour l\'analyse des idées de projet',
                'parent_id' => $dossierPhase->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => '#059669',
                'icone' => 'chart-bar'
                // profondeur et path sont calculés automatiquement par le modèle
            ]);

            // 4. Sous-sous-dossier selon le type
            $nomSousDossier = $type === 'appreciation' ?
                'Analyse preliminaire de l\'impact climatique' :
                'Analyse multicritere';

            $descriptionSousDossier = $type === 'appreciation' ?
                'Documents pour l\'analyse préliminaire de l\'impact climatique des projets' :
                'Documents pour l\'analyse multicritère (AMC) des projets';

            $couleurSousDossier = $type === 'appreciation' ? '#DC2626' : '#7C3AED';
            $iconeSousDossier = $type === 'appreciation' ? 'fire' : 'adjustments';

            $sousSousDossier = Dossier::firstOrCreate([
                'nom' => $nomSousDossier,
                'parent_id' => $dossierAnalyse->id
            ], [
                'nom' => $nomSousDossier,
                'description' => $descriptionSousDossier,
                'parent_id' => $dossierAnalyse->id,
                'is_public' => true,
                'created_by' => auth()->id(),
                'couleur' => $couleurSousDossier,
                'icone' => $iconeSousDossier
                // profondeur et path sont calculés automatiquement par le modèle
            ]);

            return $sousSousDossier;

        } catch (\Exception $e) {
            // En cas d'erreur, retourner null et laisser le fichier sans dossier
            \Log::warning('Erreur lors de la création de la structure de dossiers canevas', [
                'error' => $e->getMessage(),
                'type' => $type
            ]);
            return null;
        }
    }
}
