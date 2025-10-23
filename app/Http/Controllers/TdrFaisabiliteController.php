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

        // Permissions pour la gestion des TDRs de faisabilité - CRUD de base
        /*$this->middleware('permission:voir-la-liste-des-tdrs-de-faisabilite')->only(['index']);
        $this->middleware(['permission:voir-la-liste-des-tdrs-de-faisabilite', 'permission:voir-tdr-faisabilite'])->only(['show']);
        $this->middleware('permission:soumettre-tdr-faisabilite')->only(['store']);
        $this->middleware('permission:modifier-un-tdr-de-faisabilite')->only(['update']);
        $this->middleware('permission:supprimer-un-tdr-de-faisabilite')->only(['destroy']);

        // Permissions pour la gestion des TDRs
        $this->middleware(['permission:voir-tdr-faisabilite', 'permission:telecharger-tdr-faisabilite'])->only(['getTdrDetails']);
        $this->middleware(['permission:soumettre-tdr-faisabilite', 'permission:modifier-un-tdr-de-faisabilite'])->only(['soumettreTdrs']);
        $this->middleware('permission:apprecier-un-tdr-de-faisabilite')->only(['evaluerTdrs', 'validerTdrs']);
        $this->middleware(['permission:voir-la-liste-des-tdrs-de-faisabilite', 'permission:voir-details-appreciation-des-tdrs-de-faisabilite'])->only(['getEvaluationTdr']);

        // Permissions pour les rapports de faisabilité
        $this->middleware(['permission:soumettre-un-rapport-de-faisabilite', 'permission:gerer-les-rapports-de-faisabilite', 'permission:modifier-un-rapport-de-faisabilite', 'permission:supprimer-un-rapport-de-faisabilite'])->only(['soumettreRapportFaisabilite']);
        //$this->middleware(['permission:gerer-les-rapports-de-prefaisabilite', 'permission:voir-detail-validation-une-etude-de-faisabilite'])->only(['getDetailsSoumissionRapportFaisabilite']);
        $this->middleware('permission:valider-une-etude-de-faisabilite')->only(['validerEtudeFaisabilite']);
        $this->middleware('permission:voir-detail-validation-une-etude-de-faisabilite')->only(['getDetailsValidationEtude']);*/

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
    public function getTdrDetails($projetId): JsonResponse
    {
        return $this->service->getTdrDetails($projetId);
    }

    /**
     * Soumettre les TDRs de faisabilité (SFD-014)
     */
    public function soumettreTdrs(SoumettreTdrsFaisabiliteRequest $request, $projetId): JsonResponse
    {
        return $this->service->soumettreTdrs($projetId, $request->all());
    }

    /**
     * Apprécier et évaluer les TDRs de faisabilité (SFD-015)
     */
    public function evaluerTdrs(EvaluerTdrsFaisabiliteRequest $request, $projetId): JsonResponse
    {
        return $this->service->evaluerTdrs($projetId, $request->all());
    }

    /**
     * Récupérer les détails d'évaluation d'un TDR de faisabilité
     */
    public function getEvaluationTdr($projetId): JsonResponse
    {
        return $this->service->getEvaluationTdr($projetId);
    }

    /**
     * Valider les TDRs de faisabilité
     */
    public function validerTdrs(ValiderTdrsRequest $request, $projetId): JsonResponse
    {
        return $this->service->validerTdrs($projetId, $request->all());
    }

    /**
     * Récupérer les détails de validation des TDRs
     */
    public function getDetailsValidationEtude($projetId): JsonResponse
    {
        return $this->service->getDetailsValidationEtude($projetId);
    }

    /**
     * Soumettre le rapport de faisabilité (SFD-016)
     */
    public function soumettreRapportFaisabilite(SoumettreRapportFaisabiliteRequest $request, $projetId): JsonResponse
    {
        return $this->service->soumettreRapportFaisabilite($projetId, $request->all());
    }

    /**
     * Valider l'étude de faisabilité (SFD-017)
     */
    public function validerEtudeFaisabilite(ValiderEtudeFaisabiliteRequest $request, $projetId): JsonResponse
    {
        return $this->service->validerEtudeFaisabilite($projetId, $request->all());
    }

    /**
     * Soumettre le rapport de faisabilité (SFD-012)
     */
    public function getDetailsSoumissionRapportFaisabilite($projetId): JsonResponse
    {
        return $this->service->getDetailsSoumissionRapportFaisabilite($projetId);
    }

}
