<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\projets\evaluation_ex_ante\SoumettreRapportFinalRequest;
use App\Http\Requests\projets\evaluation_ex_ante\ValiderRapportFinalRequest;
use App\Http\Requests\tdrs\SoumettreTdrsRequest;
use App\Http\Requests\tdrs\EvaluerTdrsRequest;
use App\Http\Requests\tdrs\SoumettreRapportPrefaisabiliteRequest;
use App\Http\Requests\tdrs\ValiderTdrsRequest;
use App\Http\Requests\tdrs_prefaisabilite\StoreTdrPrefaisabiliteRequest;
use App\Http\Requests\tdrs_prefaisabilite\UpdateTdrPrefaisabiliteRequest;
use App\Services\Contracts\TdrPrefaisabiliteServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TdrPrefaisabiliteController extends Controller
{
    protected TdrPrefaisabiliteServiceInterface $service;

    public function __construct(TdrPrefaisabiliteServiceInterface $service)
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

    public function store(StoreTdrPrefaisabiliteRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateTdrPrefaisabiliteRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Récupérer les détails des TDRs de préfaisabilité soumis
     */
    public function getTdrDetails(int $projetId): JsonResponse
    {
        return $this->service->getTdrDetails($projetId);
    }

    /**
     * Soumettre les TDRs de préfaisabilité (SFD-010)
     */
    public function soumettreTdrs(SoumettreTdrsRequest $request, int $projetId): JsonResponse
    {
        return $this->service->soumettreTdrs($projetId, $request->all());
    }

    /**
     * Apprécier et évaluer les TDRs de préfaisabilité (SFD-011)
     */
    public function evaluerTdrs(EvaluerTdrsRequest $request, int $projetId): JsonResponse
    {
        return $this->service->evaluerTdrs($projetId, $request->all());
    }

    /**
     * Récupérer les détails d'évaluation d'un TDR
     */
    public function getEvaluationTdr(int $projetId): JsonResponse
    {
        return $this->service->getEvaluationTdr($projetId);
    }

    /**
     * Valider les TDRs de préfaisabilité
     */
    public function validerTdrs(ValiderTdrsRequest $request, int $projetId): JsonResponse
    {
        return $this->service->validerTdrs($projetId, $request->all());
    }

    /**
     * Récupérer les détails de validation des TDRs
     */
    public function getDetailsValidation(int $projetId): JsonResponse
    {
        return $this->service->getDetailsValidation($projetId);
    }

    /**
     * Soumettre le rapport de préfaisabilité (SFD-012)
     */
    public function soumettreRapportPrefaisabilite(SoumettreRapportPrefaisabiliteRequest $request, int $projetId): JsonResponse
    {
        return $this->service->soumettreRapportPrefaisabilite($projetId, $request->all());
    }

    /**
     * Soumettre le rapport de préfaisabilité (SFD-012)
     */
    public function getDetailsSoumissionRapportPrefaisabilite(int $projetId): JsonResponse
    {
        return $this->service->getDetailsSoumissionRapportPrefaisabilite($projetId);
    }

    /**
     * Valider l'étude de préfaisabilité (SFD-013)
     */
    public function validerEtudePrefaisabilite(Request $request, int $projetId): JsonResponse
    {
        return $this->service->validerEtudePrefaisabilite($projetId, $request->all());
    }

    /**
     * Soumettre le rapport d'évaluation ex-ante (SFD-018)
     */
    public function soumettreRapportEvaluationExAnte(SoumettreRapportFinalRequest $request, int $projetId): JsonResponse
    {
        return $this->service->soumettreRapportEvaluationExAnte($projetId, $request->all());
    }

    /**
     * Valider le rapport final (SFD-019)
     */
    public function validerRapportFinal(ValiderRapportFinalRequest $request, int $projetId): JsonResponse
    {
        return $this->service->validerRapportFinal($projetId, $request->all());
    }
}