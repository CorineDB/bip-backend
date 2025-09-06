<?php

namespace App\Repositories\Contracts;

interface DocumentRepositoryInterface extends BaseRepositoryInterface
{
    // Define contract methods here

    /**
     * Get the unique fiche idée
     */
    public function getFicheIdee();

    /**
     * Get the unique fiche idée
     */
    public function getCanevasRedactionNoteConceptuelle();


    public function getCanevasAppreciationTdr();

    /**
     * Get the unique canevas d'appreciation des TDRs préfaisabilité
     */
    public function getCanevasAppreciationTdrPrefaisabilite();

    /**
     * Get the unique canevas d'appreciation des TDRs faisabilité
     */
    public function getCanevasAppreciationTdrFaisabilite();

    /**
     * Get the unique canevas de rédaction TDR préfaisabilité
     */
    //public function getCanevasRedactionTdrPrefaisabilite();

    /**
     * Get the unique canevas de rédaction TDR faisabilité
     */
    //public function getCanevasRedactionTdrFaisabilite();

    /**
     * Get the unique canevas de checklist suivi rapport préfaisabilité
     */
    public function getCanevasChecklistSuiviRapportPrefaisabilite();

    /**
     * Get the unique canevas de checklist mesures adaptation
     */
    public function getCanevasChecklistMesuresAdaptation();

    /**
     * Get the unique canevas de checklist etude faisabilite marche
     */
    public function getCanevasChecklisteEtudeFaisabiliteMarche();

    /**
     * Get the unique canevas de checklist etude faisabilite economique
     */
    public function getCanevasChecklisteEtudeFaisabiliteEconomique();

    /**
     * Get the unique canevas de checklist etude faisabilite technique
     */
    public function getCanevasChecklisteEtudeFaisabiliteTechnique();

    /**
     * Get the unique canevas de checklist etude faisabilite financiere
     */
    public function getCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere();

    /**
     * Get the unique canevas de checklist etude faisabilite organisationnelle et juridique
     */
    public function getCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique();

    /**
     * Get the unique canevas de checklist etude analyse impact environnemental et sociale
     */
    public function getCanevasChecklisteSuiviEtudeImpactEnvironnementaleEtSociale();

    /**
     * Get the unique canevas de checklist suivi assurance qualite etude faisabilite
     */
    public function getCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite();
}
