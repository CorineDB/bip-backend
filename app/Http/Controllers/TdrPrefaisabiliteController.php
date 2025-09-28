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

        // Permissions pour la gestion des TDRs de préfaisabilité - CRUD de base
        /*$this->middleware('permission:voir-la-liste-des-tdrs-de-prefaisabilite')->only(['index']);
        $this->middleware(['permission:voir-la-liste-des-tdrs-de-prefaisabilite', 'permission:voir-tdr-prefaisabilite'])->only(['show']);
        $this->middleware('permission:soumettre-tdr-prefaisabilite')->only(['store']);
        $this->middleware('permission:modifier-un-tdr-de-prefaisabilite')->only(['update']);
        $this->middleware('permission:supprimer-un-tdr-de-prefaisabilite')->only(['destroy']);

        // Permissions pour la gestion des TDRs
        $this->middleware(['permission:voir-tdr-prefaisabilite', 'permission:telecharger-tdr-prefaisabilite'])->only(['getTdrDetails']);
        $this->middleware(['permission:soumettre-tdr-prefaisabilite', 'permission:modifier-un-tdr-de-prefaisabilite'])->only(['soumettreTdrs']);
        $this->middleware('permission:apprecier-un-tdr-de-prefaisabilite')->only(['evaluerTdrs', 'validerTdrs']);
        $this->middleware(['permission:voir-la-liste-des-tdrs-de-prefaisabilite', 'permission:voir-details-appreciation-des-tdrs-de-prefaisabilite'])->only(['getEvaluationTdr']);

        // Permissions pour les rapports de préfaisabilité
        $this->middleware(['permission:soumettre-un-rapport-de-prefaisabilite', 'permission:gerer-les-rapports-de-prefaisabilite', 'permission:modifier-un-rapport-de-prefaisabilite', 'permission:supprimer-un-rapport-de-prefaisabilite'])->only(['soumettreRapportPrefaisabilite']);
        $this->middleware(['permission:gerer-les-rapports-de-prefaisabilite', 'permission:voir-detail-validation-une-etude-de-prefaisabilite'])->only(['getDetailsSoumissionRapportPrefaisabilite']);
        $this->middleware('permission:voir-detail-validation-une-etude-de-prefaisabilite')->only(['getDetailsValidationEtude']);
        $this->middleware('permission:valider-une-etude-de-prefaisabilite')->only(['validerEtudePrefaisabilite']);

        // Permissions pour les évaluations ex-ante
        $this->middleware('permission:soumettre-un-rapport-d-evaluation-ex-ante')->only(['soumettreRapportEvaluationExAnte']);
        $this->middleware('permission:valider-un-rapport-evaluation-ex-ante')->only(['validerRapportFinal']);*/
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
    public function soumettreTdrs(SoumettreTdrsRequest $request, $projetId): JsonResponse
    {
        return $this->service->soumettreTdrs($projetId, $request->all());
    }

    /**
     * Apprécier et évaluer les TDRs de préfaisabilité (SFD-011)
     */
    public function evaluerTdrs(EvaluerTdrsRequest $request, $projetId): JsonResponse
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
    public function validerTdrs(ValiderTdrsRequest $request, $projetId): JsonResponse
    {
        return $this->service->validerTdrs($projetId, $request->all());
    }

    /**
     * Récupérer les détails de validation des TDRs
     */
    public function getDetailsValidationEtude(int $projetId): JsonResponse
    {
        return $this->service->getDetailsValidationEtude($projetId);
    }

    /**
     * Soumettre le rapport de préfaisabilité (SFD-012)
     */
    public function soumettreRapportPrefaisabilite(SoumettreRapportPrefaisabiliteRequest $request, $projetId): JsonResponse
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
    public function validerEtudePrefaisabilite(Request $request, $projetId): JsonResponse
    {
        return $this->service->validerEtudePrefaisabilite($projetId, $request->all());
    }

    /**
     * Soumettre le rapport d'évaluation ex-ante (SFD-018)
     */
    public function soumettreRapportEvaluationExAnte(SoumettreRapportFinalRequest $request, $projetId): JsonResponse
    {
        return $this->service->soumettreRapportEvaluationExAnte($projetId, $request->all());
    }

    /**
     * Soumettre le rapport de préfaisabilité (SFD-012)
     */
    public function getDetailsSoumissionRapportFinale(int $projetId): JsonResponse
    {
        return $this->service->getDetailsSoumissionRapportFinale($projetId);
    }

    /**
     * Valider le rapport final (SFD-019)
     */
    public function validerRapportFinal(ValiderRapportFinalRequest $request, $projetId): JsonResponse
    {
        return $this->service->validerRapportFinal($projetId, $request->all());
    }
}
