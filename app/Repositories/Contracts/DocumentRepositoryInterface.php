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
     * Get the unique canevas de rédaction TDR préfaisabilité
     */
    public function getCanevasRedactionTdrPrefaisabilite();

    /**
     * Get the unique canevas de rédaction TDR faisabilité
     */
    public function getCanevasRedactionTdrFaisabilite();

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
    public function getCanevasChecklistEtudeFaisabiliteMarche();

    /**
     * Get the unique canevas de checklist etude faisabilite economique
     */
    public function getCanevasChecklistEtudeFaisabiliteEconomique();

    /**
     * Get the unique canevas de checklist etude faisabilite technique
     */
    public function getCanevasChecklistEtudeFaisabiliteTechnique();

    /**
     * Get the unique canevas de checklist etude faisabilite financiere
     */
    public function getCanevasChecklistEtudeFaisabiliteFinanciere();

    /**
     * Get the unique canevas de checklist etude faisabilite organisationnelle et juridique
     */
    public function getCanevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique();

    /**
     * Get the unique canevas de checklist etude faisabilite organisationnelle
     */
    public function getCanevasChecklistEtudeFaisabiliteOrganisationnelle();

    /**
     * Get the unique canevas de checklist etude analyse impact environnemental et sociale
     */
    public function getCanevasChecklistEtudeAnalyseImpactEnvironnementalEtSociale();

    /**
     * Get the unique canevas de checklist suivi assurance qualite etude faisabilite
     */
    public function getCanevasChecklistSuiviAssuranceQualiteEtudeFaisabilite();
}