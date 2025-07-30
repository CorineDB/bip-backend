<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\EvaluationCritere;
use App\Models\Evaluation;
use App\Models\User;
use App\Models\Critere;
use App\Models\Notation;
use App\Http\Resources\EvaluationCritereResource;
use Exception;

class EvaluationCritereIndividuelController extends Controller
{
    /**
     * Obtenir toutes les évaluations de critères d'un évaluateur pour une évaluation.
     */
    public function getEvaluateurCriteres($evaluationId, $evaluateurId): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);
            $evaluateur = User::findOrFail($evaluateurId);

            $evaluationCriteres = EvaluationCritere::forEvaluation($evaluationId)
                ->byEvaluateur($evaluateurId)
                ->with(['critere', 'notation', 'categorieCritere'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation' => $evaluation,
                    'evaluateur' => $evaluateur,
                    'criteres' => EvaluationCritereResource::collection($evaluationCriteres),
                    'stats' => [
                        'total' => $evaluationCriteres->count(),
                        'completed' => $evaluationCriteres->filter->isCompleted()->count(),
                        'pending' => $evaluationCriteres->filter->isPending()->count(),
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des critères',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Noter un critère spécifique pour un évaluateur.
     */
    public function noterCritere(Request $request, $evaluationId, $evaluateurId, $critereId): JsonResponse
    {
        $request->validate([
            'notation_id' => 'required|integer|exists:notations,id',
            'note' => 'nullable|string|max:500',
            'commentaire' => 'nullable|string|max:1000'
        ]);

        try {
            $evaluationCritere = EvaluationCritere::forEvaluation($evaluationId)
                ->byEvaluateur($evaluateurId)
                ->byCritere($critereId)
                ->firstOrFail();

            $notation = Notation::findOrFail($request->notation_id);

            $evaluationCritere->update([
                'notation_id' => $request->notation_id,
                'note' => $request->note ?? $notation->libelle,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Critère évalué avec succès',
                'data' => new EvaluationCritereResource($evaluationCritere->load(['critere', 'notation', 'categorieCritere']))
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'évaluation du critère',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir un critère spécifique d'un évaluateur.
     */
    public function getCritereEvaluateur($evaluationId, $evaluateurId, $critereId): JsonResponse
    {
        try {
            $evaluationCritere = EvaluationCritere::forEvaluation($evaluationId)
                ->byEvaluateur($evaluateurId)
                ->byCritere($critereId)
                ->with(['critere', 'notation', 'categorieCritere', 'evaluateur'])
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => new EvaluationCritereResource($evaluationCritere)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Critère non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Marquer tous les critères d'un évaluateur comme terminés.
     */
    public function terminerEvaluationEvaluateur($evaluationId, $evaluateurId): JsonResponse
    {
        try {
            $evaluateur = User::findOrFail($evaluateurId);
            $evaluation = Evaluation::findOrFail($evaluationId);

            $evaluationCriteres = EvaluationCritere::forEvaluation($evaluationId)
                ->byEvaluateur($evaluateurId)
                ->get();

            $pendingCriteres = $evaluationCriteres->filter->isPending();

            if ($pendingCriteres->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tous les critères doivent être évalués avant de terminer',
                    'data' => [
                        'pending_criteres' => $pendingCriteres->count(),
                        'total_criteres' => $evaluationCriteres->count()
                    ]
                ], 400);
            }

            return response()->json([
                'success' => true,
                'message' => 'Évaluation terminée pour cet évaluateur',
                'data' => [
                    'evaluateur' => $evaluateur,
                    'evaluation' => $evaluation,
                    'completed_criteres' => $evaluationCriteres->count(),
                    'completion_date' => now()
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
     * Obtenir les statistiques d'évaluation pour un évaluateur.
     */
    public function getStatsEvaluateur($evaluationId, $evaluateurId): JsonResponse
    {
        try {
            $evaluateur = User::findOrFail($evaluateurId);
            $evaluation = Evaluation::findOrFail($evaluationId);

            $evaluationCriteres = EvaluationCritere::forEvaluation($evaluationId)
                ->byEvaluateur($evaluateurId)
                ->with(['critere', 'notation'])
                ->get();

            $completed = $evaluationCriteres->filter->isCompleted();
            $pending = $evaluationCriteres->filter->isPending();

            $averageScore = $completed->map->getNumericValue()->filter()->average();

            return response()->json([
                'success' => true,
                'data' => [
                    'evaluateur' => $evaluateur,
                    'evaluation' => $evaluation,
                    'stats' => [
                        'total_criteres' => $evaluationCriteres->count(),
                        'completed_criteres' => $completed->count(),
                        'pending_criteres' => $pending->count(),
                        'completion_percentage' => $evaluationCriteres->count() > 0 
                            ? round(($completed->count() / $evaluationCriteres->count()) * 100, 2) 
                            : 0,
                        'average_score' => $averageScore ? round($averageScore, 2) : null,
                        'is_complete' => $pending->count() === 0
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir toutes les évaluations d'un critère par tous les évaluateurs.
     */
    public function getCritereAllEvaluateurs($evaluationId, $critereId): JsonResponse
    {
        try {
            $evaluation = Evaluation::findOrFail($evaluationId);
            $critere = Critere::findOrFail($critereId);

            $evaluationCriteres = EvaluationCritere::forEvaluation($evaluationId)
                ->byCritere($critereId)
                ->with(['evaluateur', 'notation', 'categorieCritere'])
                ->get();

            $completed = $evaluationCriteres->filter->isCompleted();
            $numericValues = $completed->map->getNumericValue()->filter();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'evaluation' => $evaluation,
                    'critere' => $critere,
                    'evaluations' => EvaluationCritereResource::collection($evaluationCriteres),
                    'stats' => [
                        'total_evaluateurs' => $evaluationCriteres->count(),
                        'completed_evaluations' => $completed->count(),
                        'average_score' => $numericValues->count() > 0 ? round($numericValues->average(), 2) : null,
                        'min_score' => $numericValues->count() > 0 ? $numericValues->min() : null,
                        'max_score' => $numericValues->count() > 0 ? $numericValues->max() : null,
                        'consensus_level' => $this->calculateConsensusLevel($numericValues->toArray())
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des évaluations du critère',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculer le niveau de consensus entre les évaluateurs.
     */
    private function calculateConsensusLevel(array $scores): string
    {
        if (count($scores) <= 1) {
            return 'Insuffisant';
        }

        $mean = array_sum($scores) / count($scores);
        $variance = array_sum(array_map(function($score) use ($mean) {
            return pow($score - $mean, 2);
        }, $scores)) / count($scores);

        if ($variance < 0.5) return 'Très fort';
        if ($variance < 1.0) return 'Fort';
        if ($variance < 2.0) return 'Modéré';
        return 'Faible';
    }
}