<?php

namespace App\Services;

use App\Http\Requests\evaluations\SoumettreEvaluationClimatiqueIdeeRequest;
use App\Http\Requests\evaluations\ModifierEvaluationClimatiqueRequest;
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

class EvaluationService extends BaseService implements EvaluationServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected IdeeProjetRepositoryInterface $ideeProjetRepository;

    public function __construct(
        EvaluationRepositoryInterface $repository,
        IdeeProjetRepositoryInterface $ideeProjetRepository
    )
    {
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
                'resultats_evaluation' => []
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
    public function assignEvaluateursToEvaluation(int $evaluationId, array $evaluateurIds): JsonResponse
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
                        'notation_id' => null
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
    public function getEvaluationProgress(int $evaluationId): JsonResponse
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
                    'aggregated_scores' => $aggregated,
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
    public function finalizeEvaluation(int $evaluationId): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);

            // Calculer les résultats finaux
            $aggregatedScores = $evaluation->getAggregatedScores();
            $finalResults = $this->calculateFinalResults($aggregatedScores);

            $evaluation->update([
                'resultats_evaluation' => $finalResults,
                'valider_le' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Évaluation finalisée avec succès',
                'data' => [
                    'evaluation' => new EvaluationResource($evaluation),
                    'final_results' => $finalResults
                ]
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
                'notation_id' => null
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
     * Calculate final results from aggregated scores.
     */
    private function calculateFinalResults(object $aggregatedScores): array
    {
        $results = [];

        foreach ($aggregatedScores as $critereId => $data) {
            $results[] = [
                'critere_id' => $critereId,
                'critere_nom' => $data['critere']->nom ?? 'N/A',
                'moyenne' => round($data['moyenne'], 2),
                'total_evaluateurs' => $data['total_evaluateurs'],
                'consensus' => $this->calculateConsensus($data['notes_individuelles'])
            ];
        }

        return $results;
    }

    /**
     * Calculate consensus level between evaluateurs.
     */
    private function calculateConsensus(array $notes): string
    {
        if (count($notes) <= 1) return 'N/A';

        $variance = $this->calculateVariance($notes);

        if ($variance < 1) return 'Fort consensus';
        if ($variance < 2) return 'Consensus modéré';
        return 'Consensus faible';
    }

    /**
     * Calculate variance of notes.
     */
    private function calculateVariance(array $notes): float
    {
        $mean = array_sum($notes) / count($notes);
        $variance = array_sum(array_map(function($note) use ($mean) {
            return pow($note - $mean, 2);
        }, $notes)) / count($notes);

        return $variance;
    }

    /**
     * Soumettre les réponses d'évaluation climatique pour un évaluateur.
     */
    public function soumettreEvaluationClimatique(SoumettreEvaluationClimatiqueIdeeRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $ideeProjetId = $request->route('ideeProjetId');
            $evaluateurId = auth()->id();
            $reponses = $request->validated()['reponses'];

            // Vérifier que l'évaluation climatique existe
            $evaluation = Evaluation::where('projetable_type', 'App\Models\IdeeProjet')
                ->where('projetable_id', $ideeProjetId)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            // Vérifier que l'évaluateur est assigné à cette évaluation
            $isAssigned = EvaluationCritere::where('evaluation_id', $evaluation->id)
                ->where('evaluateur_id', $evaluateurId)
                ->exists();

            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas assigné comme évaluateur pour cette évaluation climatique.',
                ], 403);
            }

            // Mettre à jour les réponses
            foreach ($reponses as $reponse) {
                EvaluationCritere::where('evaluation_id', $evaluation->id)
                    ->where('critere_id', $reponse['critere_id'])
                    ->where('evaluateur_id', $evaluateurId)
                    ->update([
                        'notation_id' => $reponse['notation_id'],
                        'note' => 'Évalué',
                        'commentaire' => $reponse['commentaire'] ?? null,
                        'updated_at' => now()
                    ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réponses d\'évaluation climatique soumises avec succès',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'evaluateur_id' => $evaluateurId,
                    'reponses_soumises' => count($reponses)
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
     * Modifier les réponses d'évaluation climatique pour le responsable projet.
     */
    public function modifierEvaluationClimatiqueResponsable(ModifierEvaluationClimatiqueRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $ideeProjetId = $request->route('ideeProjetId');
            $responsableId = auth()->id();
            $reponses = $request->validated()['reponses'];

            // Vérifier que l'idée de projet existe et que l'utilisateur est bien le responsable
            $ideeProjet = $this->ideeProjetRepository->find($ideeProjetId);
            if (!$ideeProjet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Idée de projet non trouvée',
                ], 404);
            }

            if ($ideeProjet->responsableId !== $responsableId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas le responsable de cette idée de projet',
                ], 403);
            }

            // Vérifier que l'évaluation climatique existe
            $evaluation = Evaluation::where('projetable_type', 'App\Models\IdeeProjet')
                ->where('projetable_id', $ideeProjetId)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            // Créer ou mettre à jour les réponses du responsable
            foreach ($reponses as $reponse) {
                EvaluationCritere::updateOrCreate([
                    'evaluation_id' => $evaluation->id,
                    'critere_id' => $reponse['critere_id'],
                    'evaluateur_id' => $responsableId,
                ], [
                    'categorie_critere_id' => \App\Models\Critere::find($reponse['critere_id'])->categorie_critere_id,
                    'notation_id' => $reponse['notation_id'] ?? null,
                    'note' => $reponse['notation_id'] ? 'Évalué' : 'En attente',
                    'commentaire' => $reponse['commentaire'] ?? null,
                    'updated_at' => now()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Réponses du responsable projet modifiées avec succès',
                'data' => [
                    'evaluation_id' => $evaluation->id,
                    'responsable_id' => $responsableId,
                    'reponses_modifiees' => count($reponses)
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ressource non trouvée',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification des réponses',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}