<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface CategorieCritereServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here

    public function getGrilleEvaluationPreliminaire(): JsonResponse;
    public function updateGrilleEvaluationPreliminaire(array $data): JsonResponse;
    public function getGrilleAnalyseMultiCriteres(): JsonResponse;
    public function getGrilleAnalyseMultiCriteresAvecEvaluations(int $ideeProjetId): JsonResponse;
    public function updateGrilleAnalyseMultiCriteres(array $data): JsonResponse;
    public function getChecklistMesuresAdaptation(): JsonResponse;
    public function createOrUpdateChecklistMesuresAdaptation(array $data): JsonResponse;
    public function soumettreChecklistMesuresAdaptation(int $projetId, array $data): JsonResponse;
}