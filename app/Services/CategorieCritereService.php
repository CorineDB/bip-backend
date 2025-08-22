<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CategorieCritereRepositoryInterface;
use App\Services\Contracts\CategorieCritereServiceInterface;
use App\Http\Resources\CategorieCritereResource;
use App\Models\Critere;
use App\Models\Notation;
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

            return (new $this->resourceClass($categorieCritere->load(['criteres.notations', 'notations'])))
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
                    ->delete();
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
                            ->delete();
                    }
                }
                // Supprimer les anciens critères (et leurs notations) qui ne figurent plus dans la requête
                $criteresToDelete = $categorieCritere->criteres()->whereNotIn('id', $critereIdsFromRequest)->get();
                foreach ($criteresToDelete as $critere) {
                    // Supprimer les notations liées à ce critère
                    Notation::where('critere_id', $critere->id)->delete();
                    $critere->delete();
                }
            }

            $categorieCritere->refresh();

            DB::commit();

            return (new $this->resourceClass($categorieCritere->load(['criteres.notations', 'notations'])))
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

            return (new $this->resourceClass($grille->load(['criteres.notations', 'notations'])))
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
            $grille = $this->repository->findByAttribute('slug', 'evaluation-preliminaire-multi-projet-impact-climatique');

            $data["slug"] = 'evaluation-preliminaire-multi-projet-impact-climatique';
            if (!$grille) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grille d\'évaluation préliminaire non trouvée.',
                ], 404);
            }

            return $this->update($grille->id, $data);
        } catch (Exception $e) {
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
                'criteres.evaluations' => function($query) use ($ideeProjetId) {
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
            $grille = $this->repository->findByAttribute('slug', 'grille-analyse-multi-critere');

            $data["slug"] = 'grille-analyse-multi-critere';
            if (!$grille) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grille d\'analyse multi-critères non trouvée.',
                ], 404);
            }

            return $this->update($grille->id, $data);
        } catch (Exception $e) {
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
                'data' => new $this->resourceClass($checklistCategorie)
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
                \App\Models\ReponseEvaluation::updateOrCreate([
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

            // Traiter les sous-critères spécifiques aux secteurs si fournis
            if (isset($data['sous_criteres']) && is_array($data['sous_criteres'])) {
                foreach ($data['sous_criteres'] as $sousCritereId => $reponseData) {
                    $sousCritere = \App\Models\SousCritere::find($sousCritereId);
                    if (!$sousCritere) {
                        continue;
                    }

                    // Vérifier que le sous-critère appartient à un critère de la checklist
                    if (!$checklistCategorie->criteres()->where('id', $sousCritere->critere_id)->exists()) {
                        continue;
                    }

                    $notation = \App\Models\Notation::find($reponseData['notation_id'] ?? null);
                    if (!$notation) {
                        continue;
                    }

                    // Enregistrer la réponse du sous-critère
                    \App\Models\ReponseSousCritere::updateOrCreate([
                        'evaluation_id' => $evaluation->id,
                        'sous_critere_id' => $sousCritere->id
                    ], [
                        'notation_id' => $notation->id,
                        'commentaire' => $reponseData['commentaire'] ?? null,
                        'score' => $notation->valeur
                    ]);
                }
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
}
