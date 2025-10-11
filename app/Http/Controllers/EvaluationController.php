<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\evaluations\AMCRequest;
use App\Http\Requests\evaluations\StoreEvaluationRequest;
use App\Http\Requests\evaluations\UpdateEvaluationRequest;;
use App\Http\Requests\evaluations\SoumettreEvaluationClimatiqueIdeeRequest;
use App\Http\Requests\evaluations\SoumettreEvaluationPertinenceRequest;
use App\Http\Requests\evaluations\ValidationIdeeProjetAProjetRequest;
use App\Http\Requests\evaluations\ValidationIdeeProjetRequest;
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
     * Soumettre les réponses d'évaluation climatique (Analyste DGPD).
     */
    public function appliquerAMC(
        AMCRequest $request,
        $ideeProjetId
    ): JsonResponse {
        return $this->service->appliquerAMC($request->all(), $ideeProjetId);
    }

    /**
     * Soumettre les réponses d'évaluation climatique (Analyste DGPD).
     */
    public function getDashboardAMC(
        $ideeProjetId
    ): JsonResponse {
        return $this->service->getDashboardAMC($ideeProjetId);
    }

    public function validerIdeeDeProjet(
        ValidationIdeeProjetRequest $request,
        $ideeProjetId
    ): JsonResponse {
        return $this->service->validerIdeeDeProjet($ideeProjetId, $request->all());
    }

    public function validationIdeeDeProjetAProjet(
        ValidationIdeeProjetAProjetRequest $request,
        $ideeProjetId
    ): JsonResponse {
        return $this->service->validationIdeeDeProjetAProjet($ideeProjetId, $request->all());
    }

    public function getDecisionsValiderIdeeDeProjet($ideeProjetId): JsonResponse
    {
        return $this->service->getDecisionsValiderIdeeDeProjet($ideeProjetId);
    }

    public function getDecisionsValidationIdeeDeProjetAProjet($ideeProjetId): JsonResponse
    {
        return $this->service->getDecisionsValidationIdeeDeProjetAProjet($ideeProjetId);
    }

    /**
     * Dashboard responsable : informations complètes évaluation climatique.
     */
    public function getDashboardEvaluationClimatique($ideeProjetId): JsonResponse
    {
        return $this->service->getDashboardEvaluationClimatique($ideeProjetId);
    }

    /**
     * Dashboard évaluation de pertinence.
     */
    public function getDashboardEvaluationPertinence($ideeProjetId): JsonResponse
    {
        return $this->service->getDashboardEvaluationPertinence($ideeProjetId);
    }

    /**
     * Soumettre l'évaluation de pertinence.
     */
    public function soumettreEvaluationPertinence(SoumettreEvaluationPertinenceRequest $request, $ideeProjetId): JsonResponse
    {
        return $this->service->soumettreEvaluationPertinence($request->all(), $ideeProjetId);
    }

    /**
     * Finaliser l'auto-évaluation de pertinence.
     */
    public function finaliserAutoEvaluationPertinence($evaluationId): JsonResponse
    {
        return $this->service->finaliserAutoEvaluationPertinence($evaluationId);
    }

    public function finalizeEvaluation($ideeProjetId) : JsonResponse {
        return $this->service->finalizeEvaluation($ideeProjetId);
    }

    public function refaireAutoEvaluationClimatique($ideeProjetId): JsonResponse {
        return $this->service->refaireAutoEvaluationClimatique($ideeProjetId);
    }

    public function refaireAutoEvaluationPertinence($ideeProjetId): JsonResponse {
        return $this->service->refaireAutoEvaluationPertinence($ideeProjetId);
    }
}
