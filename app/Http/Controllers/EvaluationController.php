<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\evaluations\StoreEvaluationRequest;
use App\Http\Requests\evaluations\UpdateEvaluationRequest;
use App\Http\Requests\evaluations\CreateEvaluationWithEvaluateursRequest;
use App\Http\Requests\evaluations\AssignEvaluateursRequest;
use App\Http\Requests\evaluations\SoumettreEvaluationClimatiqueIdeeRequest;
use App\Http\Requests\evaluations\ModifierEvaluationClimatiqueRequest;
use App\Services\Contracts\EvaluationServiceInterface;
use Illuminate\Http\JsonResponse;

class EvaluationController extends Controller
{
    protected EvaluationServiceInterface $service;

    public function __construct(EvaluationServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    public function store(StoreEvaluationRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateEvaluationRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Créer une évaluation avec plusieurs évaluateurs assignés.
     */
    public function createWithEvaluateurs(CreateEvaluationWithEvaluateursRequest $request): JsonResponse
    {
        return $this->service->createEvaluationWithEvaluateurs($request->validated());
    }

    /**
     * Assigner des évaluateurs à une évaluation existante.
     */
    public function assignEvaluateurs(AssignEvaluateursRequest $request, $id): JsonResponse
    {
        return $this->service->assignEvaluateursToEvaluation($id, $request->validated()['evaluateur_ids']);
    }

    /**
     * Obtenir le progrès d'une évaluation.
     */
    public function progress($id): JsonResponse
    {
        return $this->service->getEvaluationProgress($id);
    }

    /**
     * Finaliser une évaluation et calculer les résultats.
     */
    public function finalize($id): JsonResponse
    {
        return $this->service->finalizeEvaluation($id);
    }

    /**
     * Obtenir les évaluateurs d'une évaluation.
     */
    public function evaluateurs($id): JsonResponse
    {
        try {
            $evaluation = $this->service->find($id);
            return response()->json([
                'success' => true,
                'data' => $evaluation->original['data']->evaluateurs ?? []
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des évaluateurs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer l'évaluation climatique unique d'une idée de projet (Admin/Superviseur).
     */
    public function createClimaticEvaluationForIdee(CreateEvaluationWithEvaluateursRequest $request, $ideeProjetId): JsonResponse
    {
        try {
            // Vérifier que l'idée de projet existe
            $ideeProjet = \App\Models\IdeeProjet::findOrFail($ideeProjetId);

            // Vérifier si une évaluation climatique existe déjà
            $existingEvaluation = \App\Models\Evaluation::where('projetable_type', 'App\Models\IdeeProjet')
                ->where('projetable_id', $ideeProjetId)
                ->where('type_evaluation', 'climatique')
                ->first();

            if ($existingEvaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Une évaluation climatique existe déjà pour cette idée de projet',
                    'data' => [
                        'existing_evaluation' => new \App\Http\Resources\EvaluationResource($existingEvaluation),
                        'idee_projet' => $ideeProjet
                    ]
                ], 409); // Conflict
            }

            // Préparer les données pour l'évaluation climatique
            $evaluationData = array_merge($request->validated(), [
                'type_evaluation' => 'climatique',
                'projetable_type' => 'App\Models\IdeeProjet',
                'projetable_id' => $ideeProjetId,
                'commentaire' => $request->input('commentaire', 'Évaluation climatique de l\'idée de projet: ' . $ideeProjet->sigle)
            ]);

            return $this->service->createEvaluationWithEvaluateurs($evaluationData);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Idée de projet non trouvée',
                'error' => 'L\'idée de projet avec l\'ID ' . $ideeProjetId . ' n\'existe pas.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'évaluation climatique',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permettre à un évaluateur de commencer/continuer une évaluation climatique.
     */
    public function startOrContinueClimaticEvaluation(Request $request, $ideeProjetId, $evaluateurId): JsonResponse
    {
        $request->validate([
            'criteres_ids' => 'nullable|array',
            'criteres_ids.*' => 'integer|exists:criteres,id'
        ]);

        try {
            $ideeProjet = \App\Models\IdeeProjet::findOrFail($ideeProjetId);
            $evaluateur = \App\Models\User::findOrFail($evaluateurId);

            // Vérifier qu'une évaluation climatique existe
            $evaluation = \App\Models\Evaluation::where('projetable_type', 'App\Models\IdeeProjet')
                ->where('projetable_id', $ideeProjetId)
                ->where('type_evaluation', 'climatique')
                ->first();

            if (!$evaluation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune évaluation climatique n\'a été créée pour cette idée de projet',
                    'data' => [
                        'idee_projet' => $ideeProjet,
                        'suggestion' => 'L\'administrateur doit d\'abord créer l\'évaluation et assigner les évaluateurs'
                    ]
                ], 404);
            }

            // Vérifier que l'utilisateur est bien assigné à cette évaluation
            $existingCriteres = \App\Models\EvaluationCritere::where('evaluation_id', $evaluation->id)
                ->where('evaluateur_id', $evaluateurId)
                ->with(['critere', 'notation', 'categorieCritere'])
                ->get();

            if ($existingCriteres->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous n\'êtes pas assigné comme évaluateur pour cette évaluation climatique',
                ], 403);
            }

            // Si des critères spécifiques sont demandés, les filtrer
            if ($request->has('criteres_ids') && !empty($request->criteres_ids)) {
                $existingCriteres = $existingCriteres->whereIn('critere_id', $request->criteres_ids);
            }

            return response()->json([
                'success' => true,
                'message' => 'Évaluation climatique accessible',
                'data' => [
                    'idee_projet' => $ideeProjet,
                    'evaluation' => new \App\Http\Resources\EvaluationResource($evaluation),
                    'evaluateur' => $evaluateur,
                    'criteres_assignes' => \App\Http\Resources\EvaluationCritereResource::collection($existingCriteres),
                    'stats' => [
                        'total_criteres' => $existingCriteres->count(),
                        'completed_criteres' => $existingCriteres->filter->isCompleted()->count(),
                        'pending_criteres' => $existingCriteres->filter->isPending()->count(),
                        'completion_percentage' => $existingCriteres->count() > 0
                            ? round(($existingCriteres->filter->isCompleted()->count() / $existingCriteres->count()) * 100, 2)
                            : 0
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ressource non trouvée',
                'error' => $e->getMessage()
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'accès à l\'évaluation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir l'évaluation climatique unique d'une idée de projet.
     */
    public function getClimaticEvaluationForIdee($ideeProjetId): JsonResponse
    {
        try {
            $ideeProjet = \App\Models\IdeeProjet::findOrFail($ideeProjetId);

            $evaluation = \App\Models\Evaluation::where('projetable_type', 'App\Models\IdeeProjet')
                ->where('projetable_id', $ideeProjetId)
                ->where('type_evaluation', 'climatique')
                ->with(['evaluateurs', 'evaluationCriteres.evaluateur', 'evaluationCriteres.critere', 'evaluationCriteres.notation'])
                ->first();

            if (!$evaluation) {
                return response()->json([
                    'success' => true,
                    'message' => 'Aucune évaluation climatique trouvée',
                    'data' => [
                        'idee_projet' => $ideeProjet,
                        'has_evaluation' => false,
                        'evaluation' => null
                    ]
                ]);
            }

            // Calculer les statistiques
            $totalCriteres = $evaluation->evaluationCriteres()->count();
            $completedCriteres = $evaluation->evaluationCriteres()
                ->whereNotNull('notation_id')
                ->where('note', '!=', 'En attente')
                ->count();

            $completionPercentage = $totalCriteres > 0 ? ($completedCriteres / $totalCriteres) * 100 : 0;

            return response()->json([
                'success' => true,
                'message' => 'Évaluation climatique récupérée avec succès',
                'data' => [
                    'idee_projet' => $ideeProjet,
                    'has_evaluation' => true,
                    'evaluation' => new \App\Http\Resources\EvaluationResource($evaluation),
                    'stats' => [
                        'completion_percentage' => round($completionPercentage, 2),
                        'total_criteres' => $totalCriteres,
                        'completed_criteres' => $completedCriteres,
                        'is_finalized' => !is_null($evaluation->valider_le)
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Idée de projet non trouvée',
                'error' => 'L\'idée de projet avec l\'ID ' . $ideeProjetId . ' n\'existe pas.'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de l\'évaluation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour l'évaluation climatique d'une idée de projet.
     */
    public function updateClimaticEvaluationForIdee($ideeProjetId, $evaluationId): JsonResponse
    {
        try {
            $ideeProjet = \App\Models\IdeeProjet::findOrFail($ideeProjetId);

            // Vérifier que l'évaluation appartient bien à cette idée de projet
            $evaluation = \App\Models\Evaluation::where('id', $evaluationId)
                ->where('projetable_type', 'App\Models\IdeeProjet')
                ->where('projetable_id', $ideeProjetId)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            return $this->service->getEvaluationProgress($evaluationId);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Évaluation climatique non trouvée pour cette idée de projet',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finaliser l'évaluation climatique d'une idée de projet.
     */
    public function finalizeClimaticEvaluationForIdee($ideeProjetId): JsonResponse
    {
        try {
            $ideeProjet = \App\Models\IdeeProjet::findOrFail($ideeProjetId);

            $evaluation = \App\Models\Evaluation::where('projetable_type', 'App\Models\IdeeProjet')
                ->where('projetable_id', $ideeProjetId)
                ->where('type_evaluation', 'climatique')
                ->firstOrFail();

            return $this->service->finalizeEvaluation($evaluation->id);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Évaluation climatique non trouvée pour cette idée de projet',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la finalisation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Soumettre les réponses d'évaluation climatique (Évaluateur).
     */
    public function soumettreEvaluationClimatique(
        SoumettreEvaluationClimatiqueIdeeRequest $request, 
        $ideeProjetId
    ): JsonResponse {
        return $this->service->soumettreEvaluationClimatique($request);
    }

    /**
     * Modifier les réponses d'évaluation climatique (Responsable Projet).
     */
    public function modifierEvaluationClimatique(
        ModifierEvaluationClimatiqueRequest $request, 
        $ideeProjetId
    ): JsonResponse {
        return $this->service->modifierEvaluationClimatiqueResponsable($request);
    }
}