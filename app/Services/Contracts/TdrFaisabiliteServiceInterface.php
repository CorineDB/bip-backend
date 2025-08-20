<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface TdrFaisabiliteServiceInterface
{
    /**
     * Soumettre les TDRs de faisabilité
     */
    public function soumettreTdrs(int $projetId, array $data): JsonResponse;

    /**
     * Apprécier et évaluer les TDRs de faisabilité
     */
    public function evaluerTdrs(int $projetId, array $data): JsonResponse;

    /**
     * Récupérer les détails d'évaluation d'un TDR de faisabilité
     */
    public function getEvaluationTdr(int $projetId): JsonResponse;

    /**
     * Soumettre le rapport de faisabilité (SFD-016)
     */
    public function soumettreRapportFaisabilite(int $projetId, array $data): JsonResponse;

    /**
     * Valider l'étude de faisabilité (SFD-017)
     */
    public function validerEtudeFaisabilite(int $projetId, array $data): JsonResponse;

    /**
     * Valider les TDRs de faisabilité (finaliser l'évaluation)
     */
    public function validerTdrs(int $projetId, array $data): JsonResponse;

    /**
     * Récupérer les détails de validation des TDRs
     */
    public function getDetailsValidation(int $projetId): JsonResponse;
}