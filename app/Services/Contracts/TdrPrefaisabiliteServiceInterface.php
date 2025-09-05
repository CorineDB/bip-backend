<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface TdrPrefaisabiliteServiceInterface extends AbstractServiceInterface
{
    /**
     * Récupérer les détails des TDRs de préfaisabilité soumis
     */
    public function getTdrDetails(int $projetId): JsonResponse;

    /**
     * Soumettre les TDRs de préfaisabilité (SFD-010)
     */
    public function soumettreTdrs(int $projetId, array $data): JsonResponse;

    /**
     * Apprécier et évaluer les TDRs de préfaisabilité (SFD-011)
     */
    public function evaluerTdrs(int $projetId, array $data): JsonResponse;

    /**
     * Soumettre le rapport de préfaisabilité (SFD-012)
     */
    public function soumettreRapportPrefaisabilite(int $projetId, array $data): JsonResponse;

    /**
     * Soum
     */
    public function getDetailsSoumissionRapportPrefaisabilite(int $projetId): JsonResponse;

    /**
     * Récupérer les détails d'évaluation d'un TDR
     */
    public function getEvaluationTdr(int $projetId): JsonResponse;

    /**
     * Valider les TDRs de préfaisabilité (finaliser l'évaluation)
     */
    public function validerTdrs(int $projetId, array $data): JsonResponse;

    /**
     * Récupérer les détails de validation des TDRs
     */
    public function getDetailsValidation(int $projetId): JsonResponse;

    /**
     * Valider l'étude de préfaisabilité (SFD-013)
     */
    public function validerEtudePrefaisabilite(int $projetId, array $data): JsonResponse;

    /**
     * Soumettre le rapport d'évaluation ex-ante (SFD-018)
     */
    public function soumettreRapportEvaluationExAnte(int $projetId, array $data): JsonResponse;

    /**
     * Valider le rapport final (SFD-019)
     */
    public function validerRapportFinal(int $projetId, array $data): JsonResponse;
}