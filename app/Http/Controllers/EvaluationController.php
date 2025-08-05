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
     * Soumettre les réponses d'évaluation climatique (Évaluateur).
     */
    public function soumettreEvaluationClimatique(
        SoumettreEvaluationClimatiqueIdeeRequest $request,
        $ideeProjetId
    ): JsonResponse {
        return $this->service->soumettreEvaluationClimatique($request->all(), $ideeProjetId);
    }

    /**
     * Dashboard responsable : informations complètes évaluation climatique.
     */
    public function getDashboardEvaluationClimatique($ideeProjetId): JsonResponse
    {
        return $this->service->getDashboardEvaluationClimatique($ideeProjetId);
    }

    public function finalizeEvaluation($ideeProjetId) : JsonResponse {
        return $this->service->finalizeEvaluation($ideeProjetId);
    }

    public function refaireAutoEvaluationClimatique($ideeProjetId): JsonResponse {
        return $this->service->refaireAutoEvaluationClimatique($ideeProjetId);
    }

    /**
     * Récupérer les critères d'un évaluateur pour une évaluation.
     */
    public function getEvaluateurCriteres($ideeProjetId, $evaluateurId = null): JsonResponse
    {
        return $this->service->getEvaluateurCriteres($ideeProjetId, $evaluateurId);
    }
}