<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface DocumentServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here
    public function ficheIdee(): JsonResponse;

    public function createOrUpdateFicheIdee(array $data): JsonResponse;

    public function canevasRedactionNoteConceptuelle(): JsonResponse;

    public function createOrUpdateCanevasRedactionNoteConceptuelle(array $data): JsonResponse;

    public function canevasAppreciationTdr(): JsonResponse;

    public function createOrUpdateCanevasAppreciationTdr(array $data): JsonResponse;

    public function canevasRedactionTdrPrefaisabilite(): JsonResponse;

    public function createOrUpdateCanevasRedactionTdrPrefaisabilite(array $data): JsonResponse;

    public function configurerChecklistTdrPrefaisabilite(array $data): JsonResponse;

    public function canevasRedactionTdrFaisabilite(): JsonResponse;

    public function createOrUpdateCanevasRedactionTdrFaisabilite(array $data): JsonResponse;

    public function configurerChecklistTdrFaisabilite(array $data): JsonResponse;

    public function canevasChecklistSuiviRapportPrefaisabilite(): JsonResponse;

    public function createOrUpdateCanevasChecklistSuiviRapportPrefaisabilite(array $data): JsonResponse;

    public function canevasChecklistMesuresAdaptation(): JsonResponse;

    public function createOrUpdateCanevasChecklistMesuresAdaptation(array $data): JsonResponse;

    public function canevasChecklistEtudeFaisabiliteMarche(): JsonResponse;

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteMarche(array $data): JsonResponse;

    public function canevasChecklistEtudeFaisabiliteEconomique(): JsonResponse;

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteEconomique(array $data): JsonResponse;

    public function canevasChecklistEtudeFaisabiliteTechnique(): JsonResponse;

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteTechnique(array $data): JsonResponse;

    public function canevasChecklistEtudeFaisabiliteFinanciere(): JsonResponse;

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteFinanciere(array $data): JsonResponse;

    public function canevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique(): JsonResponse;

    public function createOrUpdateCanevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique(array $data): JsonResponse;

    public function canevasChecklistEtudeAnalyseImpactEnvironnementalEtSociale(): JsonResponse;

    public function createOrUpdateCanevasChecklistEtudeAnalyseImpactEnvironnementalEtSociale(array $data): JsonResponse;

    public function canevasChecklistSuiviAssuranceQualiteEtudeFaisabilite(): JsonResponse;

    public function createOrUpdateCanevasChecklistSuiviAssuranceQualiteEtudeFaisabilite(array $data): JsonResponse;
}