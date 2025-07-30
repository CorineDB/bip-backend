<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface EvaluationServiceInterface extends AbstractServiceInterface
{
    public function createEvaluationWithEvaluateurs(array $data): JsonResponse;
    public function assignEvaluateursToEvaluation(int $evaluationId, array $evaluateurIds): JsonResponse;
    public function getEvaluationProgress(int $evaluationId): JsonResponse;
    public function finalizeEvaluation(int $evaluationId): JsonResponse;
}