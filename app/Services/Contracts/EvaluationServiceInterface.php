<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface EvaluationServiceInterface extends AbstractServiceInterface
{
    public function createEvaluationWithEvaluateurs(array $data): JsonResponse;
    public function assignEvaluateursToEvaluation($evaluationId, array $evaluateurIds): JsonResponse;
    public function getEvaluationProgress($evaluationId): JsonResponse;
    public function finalizeEvaluation($ideeProjetId): JsonResponse;
    public function refaireAutoEvaluationClimatique($ideeProjetId): JsonResponse;

    public function soumettreEvaluationClimatique(array $data, $ideeProjetId): JsonResponse;
    public function getEvaluateurCriteres($evaluationId, $evaluateurId = null): JsonResponse;
    public function getDashboardEvaluationClimatique($evaluationId): JsonResponse;
    public function validerIdeeDeProjet($ideeProjetId, array $attributs): JsonResponse;
}