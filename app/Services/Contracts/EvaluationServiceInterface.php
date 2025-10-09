<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface EvaluationServiceInterface extends AbstractServiceInterface
{
    public function finalizeEvaluation($ideeProjetId): JsonResponse;
    public function refaireAutoEvaluationClimatique($ideeProjetId): JsonResponse;

    public function soumettreEvaluationClimatique(array $data, $ideeProjetId): JsonResponse;
    public function getDashboardEvaluationClimatique($evaluationId): JsonResponse;
    public function validerIdeeDeProjet($ideeProjetId, array $attributs): JsonResponse;

    public function appliquerAMC(array $data, $ideeProjetId): JsonResponse;
    public function getDashboardAMC($ideeProjetId): JsonResponse;
    public function validationIdeeDeProjetAProjet($ideeProjetId, array $attributs): JsonResponse;
    public function getDecisionsValiderIdeeDeProjet($ideeProjetId): JsonResponse;
    public function getDecisionsValidationIdeeDeProjetAProjet($ideeProjetId): JsonResponse;

    public function soumettreEvaluationPertinence(array $data, $ideeProjetId): JsonResponse;
    public function finaliserAutoEvaluationPertinence($evaluationId): JsonResponse;
    public function refaireAutoEvaluationPertinence($ideeProjetId): JsonResponse;
    public function getDashboardEvaluationPertinence($ideeProjetId): JsonResponse;
}
