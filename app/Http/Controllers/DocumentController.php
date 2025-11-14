<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\documents\canevas_evaluation_ex_ante\CreateOrUpdateCanevasAppreciationRapportExAnteRequest;
use App\Http\Requests\documents\canevas_redaction_note_conceptuelle\CreateOrUpdateCanevasAppreciationNoteConceptuelleRequest;
use App\Http\Requests\documents\CreateOrUpdateCanevasAppreciationTdrRequest;
use App\Http\Requests\documents\CreateOrUpdateCanevasAppreciationTdrPrefaisabiliteRequest;
use App\Http\Requests\documents\canevas_redaction_note_conceptuelle\CreateOrUpdateCanevasRedactionNoteConceptuelleRequest;
use App\Http\Requests\documents\CreateOrUpdateCanevasAppreciationTdrFaisabiliteRequest;
use App\Http\Requests\documents\CreateOrUpdateCanevasChecklistSuiviRapportPrefaisabiliteRequest;
use App\Http\Requests\documents\etudes_faisabilite\CreateOrUpdateCanevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSocialeRequest;
use App\Http\Requests\documents\etudes_faisabilite\CreateOrUpdateCanevasChecklisteEtudeFaisabiliteEconomiqueRequest;
use App\Http\Requests\documents\etudes_faisabilite\CreateOrUpdateCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciereRequest;
use App\Http\Requests\documents\etudes_faisabilite\CreateOrUpdateCanevasChecklisteEtudeFaisabiliteMarcheRequest;
use App\Http\Requests\documents\etudes_faisabilite\CreateOrUpdateCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridiqueRequest;
use App\Http\Requests\documents\etudes_faisabilite\CreateOrUpdateCanevasChecklisteEtudeFaisabiliteTechniqueRequest;
use App\Http\Requests\documents\etudes_faisabilite\CreateOrUpdateCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabiliteRequest;
use App\Http\Requests\documents\etudes_faisabilite\CreateOrUpdateCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaireRequest;
use App\Http\Requests\documents\StoreDocumentRequest;
use App\Http\Requests\documents\UpdateDocumentRequest;
use App\Http\Requests\documents\fiches_idee\CreateOrUpdateFicheIdeeRequest;
use App\Services\Contracts\DocumentServiceInterface;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    protected DocumentServiceInterface $service;

    public function __construct(DocumentServiceInterface $service)
    {
        $this->service = $service;

        // Permissions pour la gestion des documents et canevas
        /*$this->middleware('permission:voir-la-liste-des-canevas')->only(['index', 'show', 'canevasAppreciationTdr', 'canevasChecklistSuiviRapportPrefaisabilite', 'canevasChecklisteEtudeFaisabiliteMarche', 'canevasChecklisteEtudeFaisabiliteEconomique', 'canevasChecklisteEtudeFaisabiliteTechnique', 'canevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere', 'canevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique', 'canevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale', 'canevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite', 'canevasAppreciationTdrPrefaisabilite', 'canevasAppreciationTdrFaisabilite', 'canevasAppreciationNoteConceptuelle']);
        $this->middleware('permission:gerer-les-canevas')->only(['store', 'update', 'destroy']);
        $this->middleware('permission:creer-un-canevas')->only(['createOrUpdateFicheIdee', 'createOrUpdateCanevasRedactionNoteConceptuelle', 'createOrUpdateCanevasAppreciationTdr', 'createOrUpdateCanevasChecklistSuiviRapportPrefaisabilite', 'createOrUpdateCanevasChecklisteEtudeFaisabiliteMarche', 'createOrUpdateCanevasChecklisteEtudeFaisabiliteEconomique', 'createOrUpdateCanevasChecklisteEtudeFaisabiliteTechnique', 'createOrUpdateCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere', 'createOrUpdateCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique', 'createOrUpdateCanevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale', 'createOrUpdateCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite', 'createOrUpdateCanevasAppreciationTdrPrefaisabilite', 'createOrUpdateCanevasAppreciationTdrFaisabilite', 'createOrUpdateCanevasAppreciationNoteConceptuelle']);
        $this->middleware('permission:modifier-un-canevas')->only(['createOrUpdateFicheIdee', 'createOrUpdateCanevasRedactionNoteConceptuelle']);

        // Permissions spécifiques pour les canevas liés aux actions IdeeProjet
        $this->middleware('permission:creer-une-idee-de-projet|modifier-une-idee-de-projet|voir-la-liste-des-idees-de-projet|voir-la-liste-des-canevas|consulter-le-canevas-de-la-fiche-idee-de-projet')->only(['ficheIdee']);

        // Permissions spécifiques pour les canevas liés aux actions NoteConceptuelle
        $this->middleware('permission:creer-une-note-conceptuelle|rediger-une-note-conceptuelle|modifier-une-note-conceptuelle|voir-la-liste-des-notes-conceptuelle|voir-la-liste-des-canevas')->only(['canevasRedactionNoteConceptuelle']);*/
    }

    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateDocumentRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Récupérer le template de fiche d'idée de projet
     *
     * @OA\Get(
     *     path="/api/fiches-idee",
     *     tags={"Documents - Templates"},
     *     summary="Récupérer le template de fiche d'idée",
     *     description="Récupère la structure du formulaire dynamique pour la saisie des fiches d'idées de projet avec tous les champs et sections configurés",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Template de fiche d'idée récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Template de fiche d'idée récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Fiche d'idée de projet - Version 2024"),
     *                 @OA\Property(property="description", type="string", example="Formulaire standardisé pour la saisie des idées de projet"),
     *                 @OA\Property(property="type", type="string", example="formulaire"),
     *                 @OA\Property(property="sections", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="intitule", type="string", example="Informations générales"),
     *                         @OA\Property(property="description", type="string", example="Informations de base sur le projet"),
     *                         @OA\Property(property="ordre_affichage", type="integer", example=1),
     *                         @OA\Property(property="champs", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="label", type="string", example="Titre du projet"),
     *                                 @OA\Property(property="attribut", type="string", example="titre_projet"),
     *                                 @OA\Property(property="type_champ", type="string", example="text"),
     *                                 @OA\Property(property="is_required", type="boolean", example=true),
     *                                 @OA\Property(property="placeholder", type="string", example="Saisir le titre du projet"),
     *                                 @OA\Property(property="ordre_affichage", type="integer", example=1),
     *                                 @OA\Property(property="meta_options", type="object",
     *                                     @OA\Property(property="validations_rules", type="object",
     *                                         @OA\Property(property="required", type="boolean", example=true),
     *                                         @OA\Property(property="maxlength", type="integer", example=255)
     *                                     ),
     *                                     @OA\Property(property="conditions", type="object",
     *                                         @OA\Property(property="visible", type="boolean", example=true),
     *                                         @OA\Property(property="disable", type="boolean", example=false)
     *                                     )
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Template non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Template de fiche d'idée non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function ficheIdee(): JsonResponse
    {
        return $this->service->ficheIdee();
    }

    /**
     * Créer ou mettre à jour le template de fiche d'idée
     *
     * @OA\Post(
     *     path="/api/fiches-idee/create-or-update",
     *     tags={"Documents - Templates"},
     *     summary="Créer/mettre à jour le template de fiche d'idée",
     *     description="Crée ou met à jour la structure du formulaire dynamique de fiche d'idée avec ses sections, champs et règles de validation",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"nom", "type"},
     *                 @OA\Property(property="nom", type="string", example="Fiche d'idée de projet - Version 2024", description="Nom du template"),
     *                 @OA\Property(property="description", type="string", example="Formulaire pour la saisie des idées de projet", description="Description du template"),
     *                 @OA\Property(property="type", type="string", example="formulaire", enum={"document", "formulaire", "grille", "checklist"}),
     *                 @OA\Property(property="sections", type="array", description="Sections du formulaire",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="intitule", type="string", example="Informations générales"),
     *                         @OA\Property(property="description", type="string", example="Section pour les informations de base"),
     *                         @OA\Property(property="ordre_affichage", type="integer", example=1),
     *                         @OA\Property(property="type", type="string", example="standard"),
     *                         @OA\Property(property="champs", type="array", description="Champs de la section",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="label", type="string", example="Titre du projet"),
     *                                 @OA\Property(property="attribut", type="string", example="titre_projet", description="Attribut unique du champ (doit être dans la liste autorisée)"),
     *                                 @OA\Property(property="type_champ", type="string", example="text", enum={"text", "textarea", "select", "checkbox", "radio", "date", "number", "email", "file"}),
     *                                 @OA\Property(property="placeholder", type="string", example="Saisir le titre du projet"),
     *                                 @OA\Property(property="is_required", type="boolean", example=true),
     *                                 @OA\Property(property="ordre_affichage", type="integer", example=1),
     *                                 @OA\Property(property="meta_options", type="object",
     *                                     @OA\Property(property="validations_rules", type="object",
     *                                         @OA\Property(property="required", type="boolean", example=true)
     *                                     ),
     *                                     @OA\Property(property="conditions", type="object",
     *                                         @OA\Property(property="visible", type="boolean", example=true),
     *                                         @OA\Property(property="disable", type="boolean", example=false),
     *                                         @OA\Property(property="conditions", type="array", @OA\Items(type="object"))
     *                                     ),
     *                                     @OA\Property(property="configs", type="object")
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="champs", type="array", description="Champs racines (hors sections)",
     *                     @OA\Items(type="object", description="Structure identique aux champs des sections")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Template créé/mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Template de fiche d'idée créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Fiche d'idée de projet - Version 2024")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="attributs_manquants", type="array",
     *                     @OA\Items(type="string", example="Le champ avec l'attribut 'titre_projet' est obligatoire.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function createOrUpdateFicheIdee(CreateOrUpdateFicheIdeeRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateFicheIdee($request->all());
    }


    /**
     * Récupérer le template de canevas de rédaction de note conceptuelle
     *
     * @OA\Get(
     *     path="/api/canevas-de-redaction-note-conceptuelle",
     *     tags={"Documents - Templates"},
     *     summary="Récupérer le template de canevas de note conceptuelle",
     *     description="Récupère la structure du formulaire dynamique pour la rédaction des notes conceptuelles avec tous les champs et sections configurés",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Template de canevas récupéré avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Template de canevas de note conceptuelle récupéré avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="nom", type="string", example="Canevas de rédaction de note conceptuelle - V2024"),
     *                 @OA\Property(property="description", type="string", example="Template standardisé pour la rédaction des notes conceptuelles de projet"),
     *                 @OA\Property(property="type", type="string", example="formulaire"),
     *                 @OA\Property(property="sections", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="intitule", type="string", example="Contexte et justification"),
     *                         @OA\Property(property="description", type="string", example="Section décrivant le contexte du projet"),
     *                         @OA\Property(property="ordre_affichage", type="integer", example=1),
     *                         @OA\Property(property="champs", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="label", type="string", example="Contexte général"),
     *                                 @OA\Property(property="attribut", type="string", example="contexte_general"),
     *                                 @OA\Property(property="type_champ", type="string", example="textarea"),
     *                                 @OA\Property(property="is_required", type="boolean", example=true),
     *                                 @OA\Property(property="placeholder", type="string", example="Décrire le contexte général du projet"),
     *                                 @OA\Property(property="ordre_affichage", type="integer", example=1),
     *                                 @OA\Property(property="meta_options", type="object",
     *                                     @OA\Property(property="validations_rules", type="object",
     *                                         @OA\Property(property="required", type="boolean", example=true)
     *                                     ),
     *                                     @OA\Property(property="conditions", type="object",
     *                                         @OA\Property(property="visible", type="boolean", example=true),
     *                                         @OA\Property(property="disable", type="boolean", example=false)
     *                                     )
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Template non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Template de canevas de note conceptuelle non trouvé")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function canevasRedactionNoteConceptuelle(): JsonResponse
    {
        return $this->service->canevasRedactionNoteConceptuelle();
    }

    /**
     * Créer ou mettre à jour le template de canevas de note conceptuelle
     *
     * @OA\Post(
     *     path="/api/canevas-de-redaction-note-conceptuelle/create-or-update",
     *     tags={"Documents - Templates"},
     *     summary="Créer/mettre à jour le template de canevas de note conceptuelle",
     *     description="Crée ou met à jour la structure du formulaire dynamique de canevas de note conceptuelle avec ses sections, champs et règles de validation",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"nom", "type"},
     *                 @OA\Property(property="nom", type="string", example="Canevas de rédaction de note conceptuelle - V2024", description="Nom du template"),
     *                 @OA\Property(property="description", type="string", example="Template pour la rédaction des notes conceptuelles", description="Description du template"),
     *                 @OA\Property(property="type", type="string", example="formulaire", enum={"document", "formulaire", "grille", "checklist"}),
     *                 @OA\Property(property="sections", type="array", description="Sections du formulaire",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="intitule", type="string", example="Contexte et justification"),
     *                         @OA\Property(property="description", type="string", example="Section décrivant le contexte du projet"),
     *                         @OA\Property(property="ordre_affichage", type="integer", example=1),
     *                         @OA\Property(property="type", type="string", example="standard"),
     *                         @OA\Property(property="champs", type="array", description="Champs de la section",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="label", type="string", example="Contexte général"),
     *                                 @OA\Property(property="attribut", type="string", example="contexte_general", description="Attribut unique du champ"),
     *                                 @OA\Property(property="type_champ", type="string", example="textarea", enum={"text", "textarea", "select", "checkbox", "radio", "date", "number", "email", "file"}),
     *                                 @OA\Property(property="placeholder", type="string", example="Décrire le contexte général du projet"),
     *                                 @OA\Property(property="is_required", type="boolean", example=true),
     *                                 @OA\Property(property="ordre_affichage", type="integer", example=1),
     *                                 @OA\Property(property="info", type="string", example="Texte d'aide pour guider l'utilisateur"),
     *                                 @OA\Property(property="meta_options", type="object",
     *                                     @OA\Property(property="validations_rules", type="object",
     *                                         @OA\Property(property="required", type="boolean", example=true)
     *                                     ),
     *                                     @OA\Property(property="conditions", type="object",
     *                                         @OA\Property(property="visible", type="boolean", example=true),
     *                                         @OA\Property(property="disable", type="boolean", example=false),
     *                                         @OA\Property(property="conditions", type="array", @OA\Items(type="object"))
     *                                     ),
     *                                     @OA\Property(property="configs", type="object")
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="champs", type="array", description="Champs racines (hors sections)",
     *                     @OA\Items(type="object", description="Structure identique aux champs des sections")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Template créé/mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Template de canevas créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="nom", type="string", example="Canevas de rédaction de note conceptuelle - V2024")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="nom", type="array",
     *                     @OA\Items(type="string", example="Le nom du document est obligatoire.")
     *                 ),
     *                 @OA\Property(property="sections", type="array",
     *                     @OA\Items(type="string", example="Au moins une section ou un champ doit être fourni.")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function createOrUpdateCanevasRedactionNoteConceptuelle(CreateOrUpdateCanevasRedactionNoteConceptuelleRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasRedactionNoteConceptuelle($request->all());
    }

    public function canevasAppreciationTdr(): JsonResponse
    {
        return $this->service->canevasAppreciationTdr();
    }

    public function createOrUpdateCanevasAppreciationTdr(CreateOrUpdateCanevasAppreciationTdrRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasAppreciationTdr($request->all());
    }

    /*public function canevasRedactionTdrPrefaisabilite(): JsonResponse
    {
        return $this->service->canevasRedactionTdrPrefaisabilite();
    }

    public function createOrUpdateCanevasRedactionTdrPrefaisabilite(CreateOrUpdateCanevasRedactionTdrPrefaisabiliteRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasRedactionTdrPrefaisabilite($request->all());
    }

    public function configurerChecklistTdrPrefaisabilite(CreateOrUpdateChecklistTdrPrefaisabiliteRequest $request): JsonResponse
    {
        return $this->service->configurerChecklistTdrPrefaisabilite($request->all());
    }

    public function canevasRedactionTdrFaisabilite(): JsonResponse
    {
        return $this->service->canevasRedactionTdrFaisabilite();
    }

    public function createOrUpdateCanevasRedactionTdrFaisabilite(CreateOrUpdateCanevasRedactionTdrFaisabiliteRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasRedactionTdrFaisabilite($request->all());
    }

    public function configurerChecklistTdrFaisabilite(CreateOrUpdateChecklistTdrFaisabiliteRequest $request): JsonResponse
    {
        return $this->service->configurerChecklistTdrFaisabilite($request->all());
    }*/

    /**
     * Récupérer le canevas de checklist suivi rapport préfaisabilité
     */
    public function canevasChecklistSuiviRapportPrefaisabilite(): JsonResponse
    {
        return $this->service->canevasChecklistSuiviRapportPrefaisabilite();
    }

    /**
     * Créer ou mettre à jour le canevas de checklist suivi rapport préfaisabilité
     */
    public function createOrUpdateCanevasChecklistSuiviRapportPrefaisabilite(CreateOrUpdateCanevasChecklistSuiviRapportPrefaisabiliteRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklistSuiviRapportPrefaisabilite($request->all());
    }

    /**
     * Récupérer le canevas de checklist mesures adaptation
     */
    /* public function canevasChecklistMesuresAdaptation(): JsonResponse
    {
        return $this->service->canevasChecklistMesuresAdaptation();
    } */

    /**
     * Créer ou mettre à jour le canevas de checklist mesures adaptation
     */
    /* public function createOrUpdateCanevasChecklistMesuresAdaptation(CreateOrUpdateCanevasChecklistMesuresAdaptationRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklistMesuresAdaptation($request->all());
    } */




    /**
     * Récupérer le canevas de checklist mesures adaptation
     */
    public function canevasChecklisteEtudeFaisabiliteMarche(): JsonResponse
    {
        return $this->service->canevasChecklisteEtudeFaisabiliteMarche();
    }

    /**
     * Créer ou mettre à jour le canevas de checklist mesures adaptation
     */
    public function createOrUpdateCanevasChecklisteEtudeFaisabiliteMarche(CreateOrUpdateCanevasChecklisteEtudeFaisabiliteMarcheRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklisteEtudeFaisabiliteMarche($request->all());
    }

    /**
     * Récupérer le canevas de checklist mesures adaptation
     */
    public function canevasChecklisteEtudeFaisabiliteEconomique(): JsonResponse
    {
        return $this->service->canevasChecklisteEtudeFaisabiliteEconomique();
    }

    /**
     * Créer ou mettre à jour le canevas de checklist mesures adaptation
     */
    public function createOrUpdateCanevasChecklisteEtudeFaisabiliteEconomique(CreateOrUpdateCanevasChecklisteEtudeFaisabiliteEconomiqueRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklisteEtudeFaisabiliteEconomique($request->all());
    }

    /**
     * Récupérer le canevas de checklist mesures adaptation
     */
    public function canevasChecklisteEtudeFaisabiliteTechnique(): JsonResponse
    {
        return $this->service->canevasChecklisteEtudeFaisabiliteTechnique();
    }

    /**
     * Créer ou mettre à jour le canevas de checklist mesures adaptation
     */
    public function createOrUpdateCanevasChecklisteEtudeFaisabiliteTechnique(CreateOrUpdateCanevasChecklisteEtudeFaisabiliteTechniqueRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklisteEtudeFaisabiliteTechnique($request->all());
    }

    /**
     * Récupérer le canevas de checklist mesures adaptation
     */
    public function canevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere(): JsonResponse
    {
        return $this->service->canevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere();
    }

    /**
     * Créer ou mettre à jour le canevas de checklist mesures adaptation
     */
    public function createOrUpdateCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere(CreateOrUpdateCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciereRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere($request->all());
    }

    /**
     * Récupérer le canevas de checklist mesures adaptation
     */
    public function canevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique(): JsonResponse
    {
        return $this->service->canevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique();
    }

    /**
     * Créer ou mettre à jour le canevas de checklist mesures adaptation
     */
    public function createOrUpdateCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique(CreateOrUpdateCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridiqueRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique($request->all());
    }

    /**
     * Récupérer le canevas de checklist mesures adaptation
     */
    public function canevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale(): JsonResponse
    {
        return $this->service->canevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale();
    }

    /**
     * Créer ou mettre à jour le canevas de checklist mesures adaptation
     */
    public function createOrUpdateCanevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale(CreateOrUpdateCanevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSocialeRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale($request->all());
    }

    /**
     * Récupérer le canevas de checklist mesures adaptation
     */
    public function canevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite(): JsonResponse
    {
        return $this->service->canevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite();
    }

    /**
     * Créer ou mettre à jour le canevas de checklist mesures adaptation
     */
    public function createOrUpdateCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite(CreateOrUpdateCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabiliteRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite($request->all());
    }

    /**
     * Récupérer le canevas de checklist mesures adaptation
     */
    public function canevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire(): JsonResponse
    {
        return $this->service->canevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire();
    }

    /**
     * Créer ou mettre à jour le canevas de checklist mesures adaptation
     */
    public function createOrUpdateCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire(CreateOrUpdateCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaireRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire($request->all());
    }

    public function canevasChecklistesSuiviRapportEtudeFaisabilite(): JsonResponse
    {
        return $this->service->canevasChecklistesSuiviRapportEtudeFaisabilite();
    }

    /**
     * Récupérer le canevas d'appréciation TDR préfaisabilité
     */
    public function canevasAppreciationTdrPrefaisabilite(): JsonResponse
    {
        return $this->service->canevasAppreciationTdrPrefaisabilite();
    }

    /**
     * Créer ou mettre à jour le canevas d'appréciation TDR préfaisabilité
     */
    public function createOrUpdateCanevasAppreciationTdrPrefaisabilite(CreateOrUpdateCanevasAppreciationTdrPrefaisabiliteRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasAppreciationTdrPrefaisabilite($request->all());
    }

    /**
     * Récupérer le canevas d'appréciation TDR faisabilité
     */
    public function canevasAppreciationTdrFaisabilite(): JsonResponse
    {
        return $this->service->canevasAppreciationTdrFaisabilite();
    }

    /**
     * Créer ou mettre à jour le canevas d'appréciation TDR faisabilité
     */
    public function createOrUpdateCanevasAppreciationTdrFaisabilite(CreateOrUpdateCanevasAppreciationTdrFaisabiliteRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasAppreciationTdrFaisabilite($request->all());
    }

    /**
     * Récupérer le canevas d'appréciation TDR faisabilité
     */
    public function canevasAppreciationNoteConceptuelle(): JsonResponse
    {
        return $this->service->canevasAppreciationNoteConceptuelle();
    }

    /**
     * Créer ou mettre à jour le canevas d'appréciation TDR faisabilité
     */
    public function createOrUpdateCanevasAppreciationNoteConceptuelle(CreateOrUpdateCanevasAppreciationNoteConceptuelleRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasAppreciationNoteConceptuelle($request->all());
    }

    /**
     * Récupérer le canevas d'appréciation des rapports d'evaluation ex-ante
     */
    public function canevasAppreciationRapportExAnte(): JsonResponse
    {
        return $this->service->canevasAppreciationRapportExAnte();
    }

    /**
     * Créer ou mettre à jour le canevas d'appréciation des rapports d'evaluation ex-ante
     */
    public function createOrUpdateCanevasAppreciationRapportExAnte(CreateOrUpdateCanevasAppreciationRapportExAnteRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateCanevasAppreciationRapportExAnte($request->all());
    }
}
