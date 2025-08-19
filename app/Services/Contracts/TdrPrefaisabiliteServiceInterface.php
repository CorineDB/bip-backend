<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface TdrPrefaisabiliteServiceInterface
{
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
}