<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\tdrs\SoumettreTdrsRequest;
use App\Http\Requests\tdrs\EvaluerTdrsRequest;
use App\Http\Requests\tdrs\SoumettreRapportPrefaisabiliteRequest;
use App\Services\Contracts\TdrPrefaisabiliteServiceInterface;
use Illuminate\Http\JsonResponse;

class TdrPrefaisabiliteController extends Controller
{
    protected TdrPrefaisabiliteServiceInterface $service;

    public function __construct(TdrPrefaisabiliteServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Soumettre les TDRs de préfaisabilité (SFD-010)
     */
    public function soumettreTdrs(SoumettreTdrsRequest $request, int $projetId): JsonResponse
    {
        return $this->service->soumettreTdrs($projetId, $request->validated());
    }

    /**
     * Apprécier et évaluer les TDRs de préfaisabilité (SFD-011)
     */
    public function evaluerTdrs(EvaluerTdrsRequest $request, int $projetId): JsonResponse
    {
        return $this->service->evaluerTdrs($projetId, $request->validated());
    }

    /**
     * Soumettre le rapport de préfaisabilité (SFD-012)
     */
    public function soumettreRapportPrefaisabilite(SoumettreRapportPrefaisabiliteRequest $request, int $projetId): JsonResponse
    {
        return $this->service->soumettreRapportPrefaisabilite($projetId, $request->validated());
    }
}