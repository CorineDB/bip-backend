<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\faisabilite\SoumettreTdrsFaisabiliteRequest;
use App\Http\Requests\faisabilite\EvaluerTdrsFaisabiliteRequest;
use App\Http\Requests\faisabilite\SoumettreRapportFaisabiliteRequest;
use App\Http\Requests\faisabilite\ValiderEtudeFaisabiliteRequest;
use App\Http\Requests\faisabilite\ValiderTdrsRequest;
use App\Http\Requests\tdrs_faisabilite\StoreTdrFaisabiliteRequest;
use App\Http\Requests\tdrs_faisabilite\UpdateTdrFaisabiliteRequest;
use App\Services\Contracts\TdrFaisabiliteServiceInterface;
use Illuminate\Http\JsonResponse;

class TdrFaisabiliteController extends Controller
{
    protected TdrFaisabiliteServiceInterface $service;

    public function __construct(TdrFaisabiliteServiceInterface $service)
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

    public function store(StoreTdrFaisabiliteRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateTdrFaisabiliteRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Récupérer les détails des TDRs de faisabilité soumis
     */
    public function getTdrDetails(int $projetId): JsonResponse
    {
        return $this->service->getTdrDetails($projetId);
    }

    /**
     * Soumettre les TDRs de faisabilité (SFD-014)
     */
    public function soumettreTdrs(SoumettreTdrsFaisabiliteRequest $request, int $projetId): JsonResponse
    {
        return $this->service->soumettreTdrs($projetId, $request->validated());
    }

    /**
     * Apprécier et évaluer les TDRs de faisabilité (SFD-015)
     */
    public function evaluerTdrs(EvaluerTdrsFaisabiliteRequest $request, int $projetId): JsonResponse
    {
        return $this->service->evaluerTdrs($projetId, $request->validated());
    }

    /**
     * Récupérer les détails d'évaluation d'un TDR de faisabilité
     */
    public function getEvaluationTdr(int $projetId): JsonResponse
    {
        return $this->service->getEvaluationTdr($projetId);
    }

    /**
     * Valider les TDRs de faisabilité
     */
    public function validerTdrs(ValiderTdrsRequest $request, int $projetId): JsonResponse
    {
        return $this->service->validerTdrs($projetId, $request->validated());
    }

    /**
     * Récupérer les détails de validation des TDRs
     */
    public function getDetailsValidation(int $projetId): JsonResponse
    {
        return $this->service->getDetailsValidation($projetId);
    }

    /**
     * Soumettre le rapport de faisabilité (SFD-016)
     */
    public function soumettreRapportFaisabilite(SoumettreRapportFaisabiliteRequest $request, int $projetId): JsonResponse
    {
        return $this->service->soumettreRapportFaisabilite($projetId, $request->validated());
    }

    /**
     * Valider l'étude de faisabilité (SFD-017)
     */
    public function validerEtudeFaisabilite(\Illuminate\Http\Request $request, int $projetId): JsonResponse
    {
        return $this->service->validerEtudeFaisabilite($projetId, $request->all());
    }
}