<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface EvaluationServiceInterface extends AbstractServiceInterface
{
    public function createEvaluationWithEvaluateurs(array $data): JsonResponse;
    public function assignEvaluateursToEvaluation(int $evaluationId, array $evaluateurIds): JsonResponse;
    public function getEvaluationProgress(int $evaluationId): JsonResponse;
    public function finalizeEvaluation(int $evaluationId): JsonResponse;

    public function soumettreEvaluationClimatique(array $data, $ideeProjetId): JsonResponse;
    public function getEvaluateurCriteres($evaluationId, $evaluateurId = null): JsonResponse;
    public function getDashboardEvaluationClimatique(int $evaluationId): JsonResponse;
}