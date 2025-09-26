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

    /**
     * Récupérer le canevas d'appréciation des notes conceptuelle
     */
    public function canevasAppreciationNoteConceptuelle(): JsonResponse;

    /**
     * Créer ou mettre à jour le canevas d'appréciation des notes conceptuelle
     */
    public function createOrUpdateCanevasAppreciationNoteConceptuelle(array $data): JsonResponse;

    public function canevasAppreciationTdr(): JsonResponse;

    public function createOrUpdateCanevasAppreciationTdr(array $data): JsonResponse;


    /**
     * Récupérer le canevas d'appréciation des TDRs de préfaisabilité
     */
    public function canevasAppreciationTdrPrefaisabilite(): JsonResponse;

    /**
     * Récupérer le canevas d'appréciation des TDRs de faisabilité
     */
    public function canevasAppreciationTdrFaisabilite(): JsonResponse;

    /**
     * Créer ou mettre à jour le canevas d'appréciation des TDRs de préfaisabilité
     */
    public function createOrUpdateCanevasAppreciationTdrPrefaisabilite(array $data): JsonResponse;

    /**
     * Créer ou mettre à jour le canevas d'appréciation des TDRs de faisabilité
     */
    public function createOrUpdateCanevasAppreciationTdrFaisabilite(array $data): JsonResponse;

    /*
    public function canevasRedactionTdrPrefaisabilite(): JsonResponse;

    public function createOrUpdateCanevasRedactionTdrPrefaisabilite(array $data): JsonResponse;

    public function configurerChecklistTdrPrefaisabilite(array $data): JsonResponse;

    public function canevasRedactionTdrFaisabilite(): JsonResponse;

    public function createOrUpdateCanevasRedactionTdrFaisabilite(array $data): JsonResponse;

    public function configurerChecklistTdrFaisabilite(array $data): JsonResponse;
    */

    //public function canevasChecklistMesuresAdaptation(): JsonResponse;

    //public function createOrUpdateCanevasChecklistMesuresAdaptation(array $data): JsonResponse;

    public function canevasChecklistSuiviRapportPrefaisabilite(): JsonResponse;

    public function createOrUpdateCanevasChecklistSuiviRapportPrefaisabilite(array $data): JsonResponse;

    public function canevasChecklisteEtudeFaisabiliteMarche(): JsonResponse;

    public function createOrUpdateCanevasChecklisteEtudeFaisabiliteMarche(array $data): JsonResponse;

    public function canevasChecklisteEtudeFaisabiliteEconomique(): JsonResponse;

    public function createOrUpdateCanevasChecklisteEtudeFaisabiliteEconomique(array $data): JsonResponse;

    public function canevasChecklisteEtudeFaisabiliteTechnique(): JsonResponse;

    public function createOrUpdateCanevasChecklisteEtudeFaisabiliteTechnique(array $data): JsonResponse;

    public function canevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere(): JsonResponse;

    public function createOrUpdateCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere(array $data): JsonResponse;

    public function canevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique(): JsonResponse;

    public function createOrUpdateCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique(array $data): JsonResponse;

    public function canevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale(): JsonResponse;

    public function createOrUpdateCanevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale(array $data): JsonResponse;

    public function canevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite(): JsonResponse;

    public function createOrUpdateCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite(array $data): JsonResponse;

    public function canevasChecklistesSuiviRapportEtudeFaisabilite(): JsonResponse;
}
