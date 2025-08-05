<?php

namespace App\Services;

use App\Enums\StatutIdee;
use App\Http\Requests\evaluations\ModifierEvaluationClimatiqueRequest;
use App\Http\Resources\EvaluationCritereResource;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\EvaluationResource;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Repositories\Contracts\IdeeProjetRepositoryInterface;
use App\Services\Contracts\EvaluationServiceInterface;
use App\Models\Evaluation;
use App\Models\EvaluationCritere;
use App\Models\Critere;
use App\Models\Notation;
use App\Models\User;
use App\Notifications\EvaluationClimatiqueAssigneeNotification;
use App\Traits\GenerateUniqueId;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class EvaluationService extends BaseService implements EvaluationServiceInterface
{
    use GenerateUniqueId;
    protected BaseRepositoryInterface $repository;
    protected IdeeProjetRepositoryInterface $ideeProjetRepository;

    public function __construct(
        EvaluationRepositoryInterface $repository,
        IdeeProjetRepositoryInterface $ideeProjetRepository
    ) {
        parent::__construct($repository);
        $this->ideeProjetRepository = $ideeProjetRepository;
    }

    protected function getResourceClass(): string
    {
        return EvaluationResource::class;
    }

    /**
     * Create an evaluation with multiple evaluateurs and assign them to criteres.
     */
    public function createEvaluationWithEvaluateurs(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Créer l'évaluation principale
            $evaluation = Evaluation::create([
                'type_evaluation' => $data['type_evaluation'] ?? 'climatique',
                'date_debut_evaluation' => $data['date_debut_evaluation'],
                //'date_fin_evaluation' => $data['date_fin_evaluation'] ?? null,
                'projetable_type' => $data['projetable_type'],
                'projetable_id' => $data['projetable_id'],
                'evaluateur_id' => auth()->user()->id ?? null,
                'commentaire' => $data['commentaire'] ?? null,
                'resultats_evaluation' => [],
                'statut' => $data['statut'] ?? -1,
                'id_evaluation' => $data['id_evaluation'] ?? null
            ]);

            // Assigner les évaluateurs aux critères
            if (isset($data['evaluateurs_criteres'])) {
                $this->assignEvaluateursToCriteres($evaluation->id, $data['evaluateurs_criteres']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Évaluation créée avec succès',
                'data' => new EvaluationResource($evaluation->load(['evaluateurs', 'evaluationCriteres.evaluateur']))
            ], 201);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'évaluation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign evaluateurs to evaluation criteres.
     */
    public function assignEvaluateursToEvaluation($evaluationId, array $evaluateurIds): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);
            $criteres = Critere::all();

            DB::beginTransaction();

            foreach ($evaluateurIds as $evaluateurId) {
                foreach ($criteres as $critere) {
                    EvaluationCritere::updateOrCreate([
                        'evaluation_id' => $evaluationId,
                        'critere_id' => $critere->id,
                        'evaluateur_id' => $evaluateurId,
                    ], [
                        'categorie_critere_id' => $critere->categorie_critere_id,
                        'note' => 'En attente',
                        'notation_id' => null,
                        'is_auto_evaluation' => false,
                        'est_archiver' => false
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Évaluateurs assignés avec succès',
                'data' => new EvaluationResource($evaluation->load(['evaluateurs', 'evaluationCriteres']))
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'assignation des évaluateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get evaluation progress for all evaluateurs.
     */
    public function getEvaluationProgress($evaluationId): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);

            $progress = $evaluation->getEvaluationsByUser();
            $aggregated = $evaluation->getAggregatedScores();

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation' => new EvaluationResource($evaluation),
                    'progress_by_user' => $progress,
                    //'aggregated_scores' => $aggregated,
                    'completion_percentage' => $this->calculateCompletionPercentage($evaluation)
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du progrès',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize evaluation and calculate final results.
     */
    public function validerIdeeDeProjet($ideeProjetId, array $attributs): JsonResponse
    {
        try {

            if (auth()->user()->type !== 'responsable-hierachique' && auth()->user()->type !== 'super-admin' && auth()->user()->type !== 'organisation') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            if ($ideeProjet->statut != StatutIdee::IDEE_DE_PROJET) {
                throw new Exception("Vous le statut de l'idee de projet est a brouillon");
            }

            // Validation idee de projet
            $evaluation = Evaluation::create([
                'type_evaluation' => 'validation-idee-projet',
                'projetable_type' => get_class($ideeProjet),
                'projetable_id' => $ideeProjet->id,
                'date_debut_evaluation' => now(),
                'date_fin_evaluation' => now(),
                'valider_le' =>  now(),
                'evaluateur_id' => auth()->user()->id,
                'valider_par' => auth()->user()->id,
                'commentaire' => $attributs["commentaire"],
                'evaluation' => $attributs,
                'resultats_evaluation' => $attributs["decision"],
                'statut' => 1
            ]);

            if ($attributs["decision"] == true) {
                $ideeProjet->update([
                    'statut' => StatutIdee::ANALYSE
                ]);
            } else {
                $ideeProjet->update([
                    'statut' => StatutIdee::BROUILLON
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Idee de projet evaluer avec succès',
                'data' => $evaluation
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la validation de l'idee de projet",
                'error' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     * Finalize evaluation and calculate final results.
     */
    public function finalizeEvaluation($ideeProjetId): JsonResponse
    {
        try {

            if (auth()->user()->type !== 'responsable-projet' && auth()->user()->type !== 'super-admin' && auth()->user()->type !== 'organisation') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            // Vérifier que l'évaluation climatique existe
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            if ($evaluation->statut == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Auto Evaluation climatique déja validé',
                ], 400);
            }

            // Calculer les résultats finaux
            $aggregatedScores = $evaluation->getAggregatedScores();
            $finalResults = $this->calculateFinalResults($aggregatedScores);

            // Calculer et ajouter les scores
            $scoreGlobal = $this->calculateScoreGlobal($evaluation->id);
            $finalResults['score_global'] = $scoreGlobal;

            // Ajouter score climatique si c'est une évaluation climatique
            if ($evaluation->type_evaluation === 'climatique') {
                $scoreClimatique = $this->calculateScoreClimatique($evaluation->id);
                $finalResults['score_climatique'] = $scoreClimatique;
            }

            $ideeProjet->update([
                'score_climatique' => $finalResults['score_climatique']['score_climatique'],
                'identifiant_bip' => $this->generateIdentifiantBip(),
                'statut' => StatutIdee::IDEE_DE_PROJET  // Marquer comme terminée
            ]);

            $evaluation->update([
                'resultats_evaluation' => $finalResults,
                'valider_le' => now(),
                'statut' => 1  // Marquer comme terminée
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Évaluation finalisée avec succès',
                'data' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation',
                'error' => $e->getMessage()
            ], $e->getCode());
        }
    }

    /**
     * Finalize evaluation and calculate final results.
     */
    public function refaireAutoEvaluationClimatique($ideeProjetId): JsonResponse
    {
        try {

            if (auth()->user()->type !== 'responsable-projet' && auth()->user()->type !== 'super-admin' && auth()->user()->type !== 'organisation') {
                throw new Exception("Vous n'avez pas les droits d'acces pour effectuer cette action", 403);
            }

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            // Vérifier que l'évaluation climatique existe
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            if ($evaluation->statut == 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Auto Evaluation climatique déja validé',
                ], 400);
            }

            $criteres = $evaluation->evaluationCriteres()
                ->where('is_auto_evaluation', true)
                ->where('est_archiver', false)->get();

            // Récupérer les utilisateurs ayant la permission d'effectuer l'évaluation climatique
            $evaluateurs = User::whereHas('roles', function ($query) {
                    $query->whereHas('permissions', function ($subQuery) {
                        $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                    });
                })->orWhereHas('role', function ($query) {
                    $query->whereHas('permissions', function ($subQuery) {
                        $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                    });
                })->where('profilable_type', auth()->user()->profilable_type)->where('profilable_id', auth()->user()->profilable_id)->get();

            //dd($evaluateurs);

            if ($evaluateurs->count() == 0) {
                throw new Exception('Aucun évaluateur trouvé avec la permission "effectuer-evaluation-climatique-idee-projet"', 404);
            }

            // Récupérer les critères éligibles pour l'évaluation climatique
            $criteres = Critere::where(function ($query) {
                $query->whereHas('categorie_critere', function ($subQuery) {
                    $subQuery->where('slug', 'evaluation-preliminaire-multi-projet-impact-climatique');
                })->orWhere(function ($subQuery) {
                    $subQuery->whereNull('categorie_critere_id')
                        ->where('is_mandatory', true);
                });
            })->get();

            // Assigner chaque évaluateur à tous les critères
            foreach ($evaluateurs as $evaluateur) {
                foreach ($criteres as $critere) {
                    EvaluationCritere::create([
                        'evaluation_id' => $evaluation->id,
                        'critere_id' => $critere->id,
                        'evaluateur_id' => $evaluateur->id,
                        'categorie_critere_id' => $critere->categorie_critere_id,
                        'note' => 'En attente',
                        'notation_id' => null,
                        'is_auto_evaluation' => true,
                        'est_archiver' => false
                    ]);
                }
            }

            $ideeProjet->update([
                'score_climatique' => 0,
                'identifiant_bip' => null,
                'statut' => StatutIdee::BROUILLON  // Marquer comme terminée
            ]);

            $evaluation->update([
                'resultats_evaluation' => [],
                'evaluation' => [],
                'valider_le' => null,
                'statut' => 0  // Marquer comme terminée
            ]);

            $criteres->each->update(["est_archiver" => true]);

            DB::commit();

            // Notifier les évaluateurs assignés
            Notification::send($evaluateurs, new EvaluationClimatiqueAssigneeNotification($ideeProjet, $evaluation));

            return response()->json([
                'success' => true,
                'message' => 'Score climatique insatisfaisant, Invitation climatique renvoyer',
                'data' => null
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Private helper to assign evaluateurs to specific criteres.
     */
    private function assignEvaluateursToCriteres(int $evaluationId, array $evaluateursCriteres): void
    {
        foreach ($evaluateursCriteres as $assignment) {
            EvaluationCritere::create([
                'evaluation_id' => $evaluationId,
                'critere_id' => $assignment['critere_id'],
                'evaluateur_id' => $assignment['evaluateur_id'],
                'categorie_critere_id' => $assignment['categorie_critere_id'],
                'note' => 'En attente',
                'notation_id' => null,
                'is_auto_evaluation' => $assignment['is_auto_evaluation'] ?? false,
                'est_archiver' => false
            ]);
        }
    }

    /**
     * Calculate completion percentage of evaluation.
     */
    private function calculateCompletionPercentage(Evaluation $evaluation): float
    {
        $totalCriteres = $evaluation->evaluationCriteres()->count();
        $completedCriteres = $evaluation->evaluationCriteres()
            ->whereNotNull('notation_id')
            ->where('note', '!=', 'En attente')
            ->count();

        return $totalCriteres > 0 ? ($completedCriteres / $totalCriteres) * 100 : 0;
    }

    /**
     * Calculate final results from aggregated scores with ponderation.
     */
    private function calculateFinalResults(object $aggregatedScores): array
    {
        $results = [];
        $total_score_pondere = 0;
        $total_ponderation = 0;

        foreach ($aggregatedScores as $critereId => $data) {
            $score_pondere = $data['score_pondere'] ?? 0;
            $ponderation = $data['ponderation'] ?? 0;

            $results[] = [
                'critere_id' => $critereId,
                'critere_nom' => $data['critere']->intitule ?? 'N/A',
                'ponderation' => $ponderation,
                'ponderation_pct' => $ponderation . '%',
                'moyenne_evaluateurs' => round($data['moyenne_evaluateurs'] ?? 0, 2),
                'score_pondere' => round($score_pondere, 2),
                'total_evaluateurs' => $data['total_evaluateurs'],
                'evaluateurs' => $data['evaluateurs'] ?? [],
                //'consensus' => $this->calculateConsensus($data['notes_individuelles'])
            ];

            $total_score_pondere += $score_pondere;
            $total_ponderation += $ponderation;
        }

        // Calcul du score final pondéré global
        $score_final_pondere = $total_ponderation > 0 ?
            round(($total_score_pondere / $total_ponderation) * 100, 2) : 0;

        return [
            'criteres_details' => $results,
            'score_final_pondere' => $score_final_pondere,
            'total_ponderation' => $total_ponderation,
            'nombre_criteres' => count($results)
        ];
    }


    /**
     * Calculate variance of notes.
     */
    private function calculateVariance(array $notes): float
    {
        if (count($notes) <= 1) {
            return 0;
        }

        $mean = array_sum($notes) / count($notes);
        $variance = array_sum(array_map(function ($note) use ($mean) {
            return pow($note - $mean, 2);
        }, $notes)) / count($notes);

        return $variance;
    }

    /**
     * Soumettre les réponses d'évaluation climatique pour un évaluateur.
     */
    public function soumettreEvaluationClimatique(array $data, $ideeProjetId): JsonResponse
    {
        try {
            DB::beginTransaction();

            $evaluateurId = auth()->id();
            $reponses = $data['reponses'];

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            // Vérifier que l'évaluation climatique existe
            $evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            // Vérifier que l'évaluateur est assigné à cette évaluation
            /*$isAssigned = EvaluationCritere::where('evaluation_id', $evaluation->id)
                ->where('evaluateur_id', $evaluateurId)
                ->exists();

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas assigné comme évaluateur pour cette évaluation climatique.',
                ], 403);
            }*/

            // Vérifier et mettre à jour les réponses
            foreach ($reponses as $reponse) {
                // Vérifier que le critère appartient à la bonne catégorie ou est obligatoire
                $critere = Critere::with('categorie_critere')->find($reponse['critere_id']);

                if (!$critere) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Critère non trouvé avec l\'ID: ' . $reponse['critere_id'],
                    ], 400);
                }

                // Vérifier les conditions d'éligibilité du critère
                $isEligible = false;

                // Cas 1: Le critère appartient à la catégorie spécifique
                if (
                    $critere->categorie_critere &&
                    $critere->categorie_critere->slug === 'evaluation-preliminaire-multi-projet-impact-climatique'
                ) {
                    $isEligible = true;
                }

                // Cas 2: Le critère n'a pas de catégorie mais est obligatoire
                if (is_null($critere->categorie_critere_id) && $critere->is_mandatory) {
                    $isEligible = true;
                }

                if (!$isEligible) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Le critère "' . $critere->intitule . '" n\'est pas éligible pour cette évaluation climatique.',
                    ], 400);
                }

                $notation = Notation::find($reponse['notation_id']);

                if (!$notation) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Notation non trouvé avec l\'ID: ' . $reponse['notation_id'],
                    ], 400);
                }

                // Mettre à jour la réponse si le critère est éligible
                /*EvaluationCritere::firstOrCreate('evaluation_id', $evaluation->id)
                    ->where('critere_id', $reponse['critere_id'])
                    ->where('evaluateur_id', $evaluateurId)
                    ->update([
                        'evaluation_id' => $evaluation->id,
                        'evaluateur_id' => auth()->id(),
                        'notation_id' => $notation->id,
                        'note' => $notation->valeur, //'Évalué',
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'updated_at' => now()
                    ]);*/
                EvaluationCritere::updateOrCreate(
                    [
                        'evaluation_id' => $evaluation->id,
                        'evaluateur_id' => $evaluateurId,
                        'categorie_critere_id' => $reponse["categorie_critere_id"],
                        'critere_id' => $reponse['critere_id'],
                    ],
                    [
                        'notation_id' => $notation->id,
                        'note' => $notation->valeur,
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'is_auto_evaluation' => $reponse['is_auto_evaluation'] ?? false,
                        'est_archiver' => false,
                        'updated_at' => now(),
                    ]
                );
            }

            $evaluation->refresh();

            if ($evaluation->statut == -1) {

                $evaluation->statut = 0;

                $evaluation->save();
            }

            $evaluation->refresh();

            DB::commit();

            // Récupérer les réponses de l'évaluateur connecté
            $evaluateurReponses = EvaluationCritere::forEvaluation($evaluation->id)
                ->byEvaluateur($evaluateurId)
                ->with(['critere', 'notation', 'categorieCritere'])
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Réponses d\'évaluation climatique soumises avec succès',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'reponses_soumises' => count($reponses),
                    'evaluateur_reponses' => EvaluationCritereResource::collection($evaluateurReponses),
                    'evaluateur_stats' => [
                        'total_criteres' => $evaluateurReponses->count(),
                        'criteres_evalues' => $evaluateurReponses->filter->isCompleted()->count(),
                        'criteres_en_attente' => $evaluateurReponses->filter->isPending()->count(),
                        'taux_completion' => $evaluateurReponses->count() > 0 ?
                            round(($evaluateurReponses->filter->isCompleted()->count() / $evaluateurReponses->count()) * 100, 2) : 0
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Évaluation climatique non trouvée pour cette idée de projet',
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission des réponses',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir toutes les évaluations de critères d'un évaluateur pour une évaluation.
     */
    public function getEvaluateurCriteres($evaluationId, $evaluateurId = null): JsonResponse
    {
        try {
            $evaluateurId = $evaluateurId ?? auth()->id();

            $evaluation = $this->repository->findOrFail($evaluationId);

            $evaluationCriteres = EvaluationCritere::forEvaluation($evaluationId)
                ->byEvaluateur($evaluateurId)
                ->with(['critere', 'notation', 'categorieCritere', 'evaluateur'])
                ->get();

            // Calculer le score climatique si c'est une évaluation climatique
            $scoreClimatique = null;
            if ($evaluation->type_evaluation === 'climatique') {
                $scoreClimatique = $this->calculateScoreClimatique($evaluationId);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation' => new EvaluationResource($evaluation),
                    'evaluateur_id' => $evaluateurId,
                    'evaluateur_reponses' => EvaluationCritereResource::collection($evaluationCriteres),
                    'evaluateur_stats' => [
                        'total_criteres' => $evaluationCriteres->count(),
                        'criteres_evalues' => $evaluationCriteres->filter->isCompleted()->count(),
                        'criteres_en_attente' => $evaluationCriteres->filter->isPending()->count(),
                        'taux_completion' => $evaluationCriteres->count() > 0 ?
                            round(($evaluationCriteres->filter->isCompleted()->count() / $evaluationCriteres->count()) * 100, 2) : 0,
                        'score_pondere_evaluateur' => $this->calculateEvaluateurScorePondere($evaluationCriteres)
                    ],
                    'score_global' => $this->calculateScoreGlobal($evaluationId),
                    'score_climatique' => $scoreClimatique
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des critères de l\'évaluateur',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer le score pondéré d'un évaluateur spécifique.
     */
    private function calculateEvaluateurScorePondere($evaluationCriteres): array
    {
        $criteres_avec_score = $evaluationCriteres->filter->isCompleted();

        if ($criteres_avec_score->isEmpty()) {
            return [
                'score_total' => 0,
                'ponderation_totale' => 0,
                'score_final' => 0,
                'nombre_criteres_evalues' => 0
            ];
        }

        $score_total = 0;
        $ponderation_totale = 0;

        foreach ($criteres_avec_score as $evaluationCritere) {
            $note = $evaluationCritere->getNumericValue() ?? 0;
            $ponderation = $evaluationCritere->critere->ponderation ?? 0;

            $score_total += $note * ($ponderation / 100);
            $ponderation_totale += $ponderation;
        }

        $score_final = $ponderation_totale > 0 ?
            round(($score_total * 100) / $ponderation_totale, 2) : 0;

        return [
            'score_total' => round($score_total, 2),
            'ponderation_totale' => $ponderation_totale,
            'score_final' => $score_final,
            'nombre_criteres_evalues' => $criteres_avec_score->count()
        ];
    }

    /**
     * Dashboard pour responsable : informations complètes de l'évaluation climatique.
     */
    public function getDashboardEvaluationClimatique($ideeProjetId): JsonResponse
    {
        try {

            /*$evaluateurs = User::whereHas('roles', function ($query) {
                $query->whereHas('permissions', function ($subQuery) {
                    $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                });
            })->orWhereHas('role', function ($query) {
                $query->whereHas('permissions', function ($subQuery) {
                    $subQuery->where('slug', 'effectuer-evaluation-climatique-idee-projet');
                });
            })->get();


            return response()->json([
                'success' => true,
                'data' => $evaluateurs
            ]);*/

            $ideeProjet = $this->ideeProjetRepository->findOrFail($ideeProjetId);

            $evaluation = $ideeProjet->evaluations()
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            // Vérifier que l'évaluation climatique existe
            /*$evaluation = Evaluation::where('projetable_type', get_class($ideeProjet))
                ->where('projetable_id', $ideeProjet->id)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();*/

            /*$evaluation = Evaluation::with(['projetable', 'evaluateur', 'validator'])
                ->findOrFail($evaluationId);*/

            // Vérifier que c'est une évaluation climatique
            if ($evaluation->type_evaluation !== 'climatique') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette évaluation n\'est pas de type climatique'
                ], 400);
            }

            // Récupérer tous les critères avec leurs évaluations
            $evaluationCriteres = EvaluationCritere::forEvaluation($evaluation->id)
                ->with(['critere', 'notation', 'categorieCritere', 'evaluateur'])
                ->get();

            // Calculs globaux
            $aggregatedScores = $evaluation->getAggregatedScores();
            $finalResults = $this->calculateFinalResults($aggregatedScores);
            $completionPercentage = $this->calculateCompletionPercentage($evaluation);

            // Calculer les scores
            $scoreGlobal = $this->calculateScoreGlobal($evaluation->id);
            $scoreClimatique = $this->calculateScoreClimatique($evaluation->id);

            // Progression par évaluateur
            $progressionParEvaluateur = $this->calculateProgressionParEvaluateur($evaluationCriteres);

            // Score pondéré par critère
            $scoresPondereParCritere = $this->calculateScoresPondereParCritere($aggregatedScores);

            // Statistiques générales
            $totalEvaluateurs = $evaluationCriteres->pluck('evaluateur_id')->unique()->count();
            $totalCriteres = $evaluationCriteres->pluck('critere_id')->unique()->count();
            $totalEvaluationsCompletes = $evaluationCriteres->filter->isCompleted()->count();
            $totalEvaluationsAttendues = $totalEvaluateurs * $totalCriteres;

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation' => new EvaluationResource($evaluation),

                    // Scores globaux
                    'score_global' => $scoreGlobal,
                    'score_climatique' => $scoreClimatique,

                    // Taux de progression global
                    'taux_progression_global' => [
                        'pourcentage' => $completionPercentage,
                        'evaluations_completes' => $totalEvaluationsCompletes,
                        'evaluations_attendues' => $totalEvaluationsAttendues,
                        'evaluateurs_total' => $totalEvaluateurs,
                        'criteres_total' => $totalCriteres
                    ],

                    // Progression par évaluateur
                    'progression_par_evaluateur' => $progressionParEvaluateur,

                    // Score pondéré par critère
                    'scores_ponderes_par_critere' => $scoresPondereParCritere,

                    // Résultats agrégés et finaux
                    //'aggregated_scores' => $aggregatedScores,
                    'final_results' => $finalResults
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer la progression par évaluateur.
     */
    private function calculateProgressionParEvaluateur($evaluationCriteres): array
    {
        return $evaluationCriteres->groupBy('evaluateur_id')->map(function ($criteres, $evaluateurId) {
            $evaluateur = $criteres->first()->evaluateur;
            $criteres_completes = $criteres->filter->isCompleted();
            $total_criteres = $criteres->count();

            return [
                'evaluateur' => [
                    'id' => $evaluateur->id ?? $evaluateurId,
                    'nom_complet' => $evaluateur->personne->nom . ' ' . $evaluateur->personne->prenom ?? 'Inconnu',
                    'email' => $evaluateur->email ?? null
                ],
                'criteres_evalues' => $criteres_completes->count(),
                'total_criteres' => $total_criteres,
                'taux_completion' => $total_criteres > 0 ?
                    round(($criteres_completes->count() / $total_criteres) * 100, 2) : 0,
                'score_pondere_individuel' => $this->calculateEvaluateurScorePondere($criteres),

                'evaluateur_reponses' => EvaluationCritereResource::collection($criteres),

                'derniere_evaluation' => Carbon::parse($criteres->max('updated_at'))->format('Y-m-d'),
                'statut' => $criteres_completes->count() === $total_criteres ? 'Terminé' : ($criteres_completes->count() > 0 ? 'En cours' : 'Non commencé')
            ];
        })->values()->toArray();
    }

    /**
     * Calculer les scores pondérés par critère.
     */
    private function calculateScoresPondereParCritere($aggregatedScores): array
    {
        return $aggregatedScores->map(function ($data, $critereId) {
            $critere = $data['critere'];
            $moyenneEvaluateurs = $data['moyenne_evaluateurs'] ?? 0;
            $scorePondere = $data['score_pondere'] ?? 0;

            return [
                'critere_id' => $critereId,
                'critere_nom' => $critere->intitule ?? 'N/A',
                'ponderation' => $data['ponderation'] ?? 0,
                'ponderation_pct' => ($data['ponderation'] ?? 0) . '%',
                'moyenne_evaluateurs' => round($moyenneEvaluateurs, 2),
                'score_pondere' => round($scorePondere, 2),
                'total_evaluateurs' => $data['total_evaluateurs'] ?? 0,
                'notes_individuelles' => $data['notes_individuelles'] ?? [],
                //'consensus' => $this->calculateConsensus($data['notes_individuelles'] ?? []),
                'evaluateurs_liste' => $data['evaluateurs'] ?? []
            ];
        })->values()->toArray();
    }

    /**
     * Changer le statut d'une évaluation.
     */
    public function changeEvaluationStatus(int $evaluationId, int $statut): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);

            $evaluation->update(['statut' => $statut]);

            $statusText = $evaluation->getStatutTextAttribute();

            return response()->json([
                'success' => true,
                'message' => "Statut de l'évaluation changé vers: {$statusText}",
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'statut' => $evaluation->statut,
                    'statut_text' => $statusText
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de statut',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Archiver des critères d'évaluation.
     */
    public function archiveEvaluationCriteres(array $critereIds): JsonResponse
    {
        try {
            DB::beginTransaction();

            $archived = EvaluationCritere::whereIn('id', $critereIds)
                ->update(['est_archiver' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$archived} critères d'évaluation archivés avec succès",
                'data' => ['archived_count' => $archived]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'archivage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restaurer des critères d'évaluation archivés.
     */
    public function unarchiveEvaluationCriteres(array $critereIds): JsonResponse
    {
        try {
            DB::beginTransaction();

            $unarchived = EvaluationCritere::whereIn('id', $critereIds)
                ->update(['est_archiver' => false]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$unarchived} critères d'évaluation restaurés avec succès",
                'data' => ['unarchived_count' => $unarchived]
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la restauration',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les évaluations par statut.
     */
    public function getEvaluationsByStatus(int $statut): JsonResponse
    {
        try {
            $evaluations = Evaluation::where('statut', $statut)
                ->with(['evaluateur', 'validator', 'projetable'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'statut' => $statut,
                    'statut_text' => match ($statut) {
                        -1 => 'En attente',
                        0 => 'En cours',
                        1 => 'Terminée',
                        default => 'Inconnu'
                    },
                    'count' => $evaluations->count(),
                    'evaluations' => EvaluationResource::collection($evaluations)
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des évaluations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les critères d'évaluation automatiques.
     */
    public function getAutoEvaluationCriteres(int $evaluationId): JsonResponse
    {
        try {
            $criteres = EvaluationCritere::forEvaluation($evaluationId)
                ->autoEvaluation()
                ->active()
                ->with(['critere', 'notation', 'categorieCritere', 'evaluateur'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation_id' => $evaluationId,
                    'count' => $criteres->count(),
                    'auto_criteres' => EvaluationCritereResource::collection($criteres)
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des critères automatiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer le score climatique d'une évaluation.
     * Le score climatique est la moyenne pondérée de tous les critères évalués.
     */
    public function calculateScoreClimatique(int $evaluationId): array
    {
        $evaluation = Evaluation::findOrFail($evaluationId);

        // Récupérer tous les critères complétés pour cette évaluation
        $criteres = EvaluationCritere::forEvaluation($evaluation->id)
            ->completed()
            ->with(['critere', 'notation'])
            ->get();

        if ($criteres->isEmpty()) {
            return [
                'score_climatique' => 0,
                'score_pourcentage' => 0,
                'nombre_criteres_evalues' => 0,
                'ponderation_totale' => 0,
                'criteres_details' => [],
                'statut_evaluation' => 'Aucun critère évalué'
            ];
        }

        // Grouper par critère et calculer la moyenne pour chaque critère
        $criteresMoyennes = $criteres->groupBy('critere_id')->map(function ($critereEvaluations) {
            $critere = $critereEvaluations->first()->critere;
            $notes = $critereEvaluations->pluck('notation.valeur')->filter()->map(function ($note) {
                return is_numeric($note) ? (float) $note : 0;
            });

            $moyenne = $notes->average();
            $ponderation = $critere->ponderation ?? 0;

            return [
                'critere_id' => $critere->id,
                'critere_nom' => $critere->intitule,
                'moyenne_critere' => round($moyenne, 2),
                'ponderation' => $ponderation,
                'score_pondere' => $moyenne * ($ponderation / 100),
                'nombre_evaluateurs' => $critereEvaluations->count(),
                'notes_individuelles' => $notes->toArray()
            ];
        });

        // Calculer le score climatique global
        $scoreTotal = $criteresMoyennes->sum('score_pondere');
        $ponderationTotale = $criteresMoyennes->sum('ponderation');

        // Score climatique sur l'échelle utilisée (généralement sur 5)
        $scoreClimatique = $ponderationTotale > 0 ?
            round(($scoreTotal * 100) / $ponderationTotale, 2) : 0;

        return [
            'score_climatique' => $scoreClimatique,
            "score_climatique" => round($criteresMoyennes->avg('score_pondere'), 2),
            'nombre_criteres_evalues' => $criteresMoyennes->count(),
            'ponderation_totale' => $ponderationTotale,
            'criteres_details' => $criteresMoyennes->values()->toArray(),
            'statut_evaluation' => $this->getStatutScoreClimatique($scoreClimatique)
        ];
    }

    /**
     * Obtenir le score climatique d'une évaluation avec détails.
     */
    public function getScoreClimatique(int $evaluationId): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);
            $scoreData = $this->calculateScoreClimatique($evaluationId);

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation_id' => $evaluationId,
                    'type_evaluation' => $evaluation->type_evaluation,
                    'score_climatique_details' => $scoreData,
                    'evaluation_info' => [
                        'statut' => $evaluation->statut,
                        'statut_text' => $evaluation->getStatutTextAttribute(),
                        'date_debut' => $evaluation->date_debut_evaluation,
                        'date_fin' => $evaluation->date_fin_evaluation,
                        'valide_le' => $evaluation->valider_le
                    ]
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul du score climatique',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir le statut qualitatif basé sur le score climatique.
     */
    private function getStatutScoreClimatique(float $score): string
    {
        return match (true) {
            $score >= 0.67 => 'Impact climatique faible',
            default => 'Impact climatique forte'
        };
    }


    /**
     * Calculer le score global de tous les critères d'une évaluation.
     * Applicable à tous les types d'évaluations (climatique, technique, etc.)
     */
    public function calculateScoreGlobal(int $evaluationId): array
    {
        $evaluation = Evaluation::findOrFail($evaluationId);

        // Récupérer tous les critères complétés pour cette évaluation
        $criteres = EvaluationCritere::forEvaluation($evaluationId)
            ->completed()
            ->with(['critere', 'notation', 'categorieCritere'])
            ->get();

        if ($criteres->isEmpty()) {
            return [
                'score_global' => 0,
                'score_pourcentage' => 0,
                'nombre_criteres_evalues' => 0,
                'ponderation_totale' => 0,
                'criteres_details' => [],
                'scores_par_categorie' => [],
                'statut_evaluation' => 'Aucun critère évalué',
                'niveau_performance' => 'Non évalué'
            ];
        }

        // Grouper par critère et calculer la moyenne pour chaque critère
        $criteresMoyennes = $criteres->groupBy('critere_id')->map(function ($critereEvaluations) {
            $critere = $critereEvaluations->first()->critere;
            $categorie = $critereEvaluations->first()->categorieCritere;
            $notes = $critereEvaluations->pluck('notation.valeur')->filter()->map(function ($note) {
                return is_numeric($note) ? (float) $note : 0;
            });

            $moyenne = $notes->count() > 0 ? $notes->average() : 0;
            $ponderation = $critere->ponderation ?? 0;

            // Calculer variance et écart-type seulement s'il y a des notes
            $variance = 0;
            $ecartType = 0;
            if ($notes->count() > 0) {
                $variance = $this->calculateVariance($notes->toArray());
                $ecartType = round(sqrt($variance), 2);
            }

            return [
                'critere_id' => $critere->id,
                'critere_nom' => $critere->intitule,
                'categorie_id' => $categorie->id ?? null,
                'categorie_nom' => $categorie->type ?? 'Aucune catégorie',
                'moyenne_critere' => round($moyenne, 2),
                'ponderation' => $ponderation,
                'score_pondere' => $moyenne * ($ponderation / 100),
                'nombre_evaluateurs' => $critereEvaluations->count(),
                'notes_individuelles' => $notes->toArray(),
                'variance' => round($variance, 2),
                'ecart_type' => $ecartType
            ];
        });

        // Calculer les scores par catégorie
        $scoresParCategorie = $criteresMoyennes->groupBy('categorie_id')->map(function ($criteres, $categorieId) {
            $categorieName = $criteres->first()['categorie_nom'];
            $scoreTotal = $criteres->sum('score_pondere');
            $ponderationTotale = $criteres->sum('ponderation');
            $scoreMoyen = $ponderationTotale > 0 ? ($scoreTotal * 100) / $ponderationTotale : 0;

            return [
                'categorie_id' => $categorieId,
                'categorie_nom' => $categorieName,
                'nombre_criteres' => $criteres->count(),
                'score_moyen_categorie' => round($scoreMoyen, 2),
                'ponderation_totale' => $ponderationTotale,
                'score_pondere_categorie' => round($scoreTotal, 2)
            ];
        });

        // Calculer le score global
        $scoreTotal = $criteresMoyennes->sum('score_pondere');
        $ponderationTotale = $criteresMoyennes->sum('ponderation');

        // Score global sur l'échelle utilisée (généralement sur 5)
        $scoreGlobal = $ponderationTotale > 0 ?
            round(($scoreTotal * 100) / $ponderationTotale, 2) : 0;

        // Pourcentage
        $scorePourcentage = round(($scoreGlobal / 5) * 100, 2);

        return [
            'score_global' => $scoreGlobal,
            'score_pourcentage' => $scorePourcentage,
            'nombre_criteres_evalues' => $criteresMoyennes->count(),
            'ponderation_totale' => $ponderationTotale,
            'criteres_details' => $criteresMoyennes->values()->toArray(),
            'scores_par_categorie' => $scoresParCategorie->values()->toArray()
        ];
    }

    /**
     * Obtenir le score global d'une évaluation avec détails.
     */
    public function getScoreGlobal(int $evaluationId): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);
            $scoreData = $this->calculateScoreGlobal($evaluationId);

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation_id' => $evaluationId,
                    'type_evaluation' => $evaluation->type_evaluation,
                    'score_global_details' => $scoreData,
                    'evaluation_info' => [
                        'statut' => $evaluation->statut,
                        'statut_text' => $evaluation->getStatutTextAttribute(),
                        'date_debut' => $evaluation->date_debut_evaluation,
                        'date_fin' => $evaluation->date_fin_evaluation,
                        'valide_le' => $evaluation->valider_le
                    ]
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul du score global',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    //calculateScoreGlobal
    /**
     * Mettre à jour le score climatique dans les résultats de l'évaluation.
     */
    public function updateScoreClimatiqueInResults(int $evaluationId): bool
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);
            $scoreData = $this->calculateScoreClimatique($evaluationId);

            // Récupérer les résultats existants ou créer un nouveau tableau
            $resultatsExistants = $evaluation->resultats_evaluation ?? [];

            // Ajouter ou mettre à jour le score climatique
            $resultatsExistants['score_climatique'] = $scoreData;

            $evaluation->update(['resultats_evaluation' => $resultatsExistants]);

            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
