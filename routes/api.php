<?php

use Illuminate\Support\Facades\Route;

// Import all controllers
use App\Http\Controllers\ArrondissementController;
use App\Http\Controllers\CategorieDocumentController;
use App\Http\Controllers\CategorieProjetController;
use App\Http\Controllers\ChampController;
use App\Http\Controllers\CibleController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\ComposantProgrammeController;
use App\Http\Controllers\DepartementController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\EvaluationController;
use App\Http\Controllers\FinancementController;
use App\Http\Controllers\IdeeProjetController;
use App\Http\Controllers\OddController;
use App\Http\Controllers\OrganisationController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PersonneController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SecteurController;
use App\Http\Controllers\TypeInterventionController;
use App\Http\Controllers\TypeProgrammeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VillageController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DgpdController;
use App\Http\Controllers\DpafController;
use App\Http\Controllers\GroupeUtilisateurController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NoteConceptuelleController;
use App\Http\Controllers\TdrPrefaisabiliteController;
use App\Http\Controllers\TdrFaisabiliteController;
use App\Models\Village;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\Traits\ResponseJsonTrait;


// Get authenticated user
/* Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
}); */
/*
Route::prefix("auths")->group(['middleware' => ['auth:sanctum']], function () {

    Route::get('confirmation-de-compte/{email}', [AuthController::class, 'confirmationDeCompte'])->name('confirmationDeCompte');

    Route::get('activation-de-compte/{token}', [AuthController::class, 'activationDeCompte'])->name('activationDeCompte');


    Route::get('reinitialisation-de-mot-de-passe/{email}', [AuthController::class, 'verificationEmailReinitialisationMotDePasse'])->name('verificationEmailReinitialisationMotDePasse');

    Route::get('verification-de-compte/{token}', [AuthController::class, 'verificationDeCompte'])->name('verificationDeCompte');

    Route::post('reinitialisation-de-mot-de-passe', [AuthController::class, 'reinitialisationDeMotDePasse'])->name('reinitialisationDeMotDePasse');

    Route::post('authentification', [AuthController::class, 'authentification'])->name('auth.authentification'); // Route d'authentification

});
*/


Route::group(['middleware' => ['cors', 'json.response'], 'as' => 'api.'], function () {

    //Route::group(['middleware' => []], function () {


    Route::group(['prefix' => 'passport-auths', 'as' => 'auths.'], function () {

        Route::post('authentification', [OAuthController::class, 'authentification'])->name('authentification');

        Route::get('confirmation-de-compte/{email}', [OAuthController::class, 'confirmationDeCompte'])->name('auths.confirmationDeCompte');

        Route::get('activation-de-compte/{token}', [OAuthController::class, 'activationDeCompte'])->name('activationDeCompte');

        Route::get('reinitialisation-de-mot-de-passe/{email}', [OAuthController::class, 'verificationEmailReinitialisationMotDePasse'])->name('verificationEmailReinitialisationMotDePasse');

        Route::get('verification-de-compte/{token}', [OAuthController::class, 'verificationDeCompte'])->name('verificationDeCompte');

        Route::post('reinitialisation-de-mot-de-passe', [OAuthController::class, 'reinitialisationDeMotDePasse'])->name('reinitialisationDeMotDePasse');

        Route::group(['middleware' => ['auth:api']], function () {
            Route::controller('App\Http\Controllers\OAuthController')->group(function () {

                Route::post('/deconnexion', 'deconnexion')->name('deconnexion'); // Route de déconnexion

                Route::get('/utilisateur-connecte', 'utilisateurConnecte')->name('utilisateurConnecte');

                Route::get('/refresh-token', 'refresh_token')->name('refreshToken');

                Route::post('modification-de-mot-de-passe', 'modificationDeMotDePasse')->name('modificationDeMotDePasse');

                Route::get('/{id}/debloquer', 'debloquer')->name('debloquer');
            });
        });
    });
    // =============================================================================
    // API RESOURCE ROUTES - Full CRUD Controllers (23 controllers)
    // =============================================================================


    Route::group(['middleware' => ['auth:api']], function () {
        // Geographic & Administrative Resources
        Route::apiResource('arrondissements', ArrondissementController::class)->only(['index', 'show']);
        Route::apiResource('communes', CommuneController::class)->only(['index', 'show']);
        Route::apiResource('departements', DepartementController::class)->only(['index', 'show']);
        Route::apiResource('villages', VillageController::class)->only(['index', 'show']);

        Route::prefix('departements')->group(function () {
            Route::get('{id}/communes', [DepartementController::class, 'communes']);
        });

        Route::prefix('communes')->group(function () {
            Route::get('{id}/arrondissements', [CommuneController::class, 'arrondissements']);
        });

        Route::prefix('arrondissements')->group(function () {
            Route::get('{id}/villages', [ArrondissementController::class, 'villages']);
        });

        // Organization & People Management
        Route::apiResource('organisations', OrganisationController::class);
        Route::apiResource('dpaf', DpafController::class)->except(['destroy']);
        Route::apiResource('dgpd', DgpdController::class)->except(['destroy']);

        Route::controller(OrganisationController::class)->group(function () {
            Route::get('ministeres', 'ministeres');
            Route::get('ministeres/{id}/organismes_tutelle', 'organismes_tutelle');
        });

        Route::apiResource('personnes', PersonneController::class);

        // User Management & Security (read-only permissions)
        Route::apiResource('users', UserController::class);
        Route::apiResource('groupes-utilisateur', GroupeUtilisateurController::class)
            ->parameters(['groupes-utilisateur' => 'groupe_utilisateur']);

        // Notifications
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
        });

        // Routes additionnelles pour les groupes d'utilisateurs
        Route::prefix('groupes-utilisateur/{groupe_utilisateur}')->group(function () {
            Route::post('/assign-roles', [GroupeUtilisateurController::class, 'assignRoles']);
            Route::delete('/detach-roles', [GroupeUtilisateurController::class, 'detachRoles']);
            Route::post('/assign-permissions', [GroupeUtilisateurController::class, 'assignPermissions']);
            Route::delete('/detach-permissions', [GroupeUtilisateurController::class, 'detachPermissions']);
            Route::post('/add-users', [GroupeUtilisateurController::class, 'addUsers']);
            Route::delete('/remove-users', [GroupeUtilisateurController::class, 'removeUsers']);
            Route::get('/roles', [GroupeUtilisateurController::class, 'getRoles']);
            Route::get('/users', [GroupeUtilisateurController::class, 'getUsers']);
            Route::post('/create-user', [GroupeUtilisateurController::class, 'createUserInGroup']);
        });
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('permissions', PermissionController::class)->only(['index', 'show']);

        // Role-Permission Management
        Route::prefix('roles/{role}')->group(function () {
            Route::get('/permissions', [RoleController::class, 'getPermissions']);
        });

        // Project Management Core
        // Route pour le classement des idées de projet en validation (doit être avant apiResource)
        Route::get('idees-projet/classement-validation', [EvaluationController::class, 'getClassementIdeesProjetsValidation'])
            ->name('idees-projet.classement-validation');

        Route::apiResource('idees-projet', IdeeProjetController::class)
            ->parameters(['idees-projet' => 'idee_projet']);


        Route::controller(IdeeProjetController::class)->group(function () {
            Route::get('demandeurs-idees-projet', 'demandeurs');
            Route::get('?statut=', 'filterByStatut');
        });

        Route::apiResource('projets', ProjetController::class)->only(['index', 'show']);

        // Routes pour filtrer les projets par maturité
        Route::get('/projets-selectionnable', [ProjetController::class, 'projetsEnCoursMaturation'])
            ->name('projets.selectionnable');
        Route::get('/projets-mature', [ProjetController::class, 'projetsArrivesAMaturite'])
            ->name('projets.mature');

        // Routes pour les notes conceptuelles des projets
        Route::prefix('projets')->group(function () {
            Route::post('{projetId}/note-conceptuelle', [NoteConceptuelleController::class, 'createForProject']);
            Route::put('{projetId}/note-conceptuelle/{noteId}', [NoteConceptuelleController::class, 'updateForProject']);
            Route::get('{projetId}/note-conceptuelle', [NoteConceptuelleController::class, 'getForProject']);
            Route::delete('{projetId}/note-conceptuelle/{noteId}', [NoteConceptuelleController::class, 'deleteForProject']);
            Route::get('{projetId}/details-validation-note-conceptuelle/{noteId}', [NoteConceptuelleController::class, 'getValidationDetails']);
            // Route pour la validation à l'étape étude de profil
            Route::post('{projetId}/valider-etude-profil', [NoteConceptuelleController::class, 'validerEtudeProfil']);
            // Route pour la validation à l'étape étude de profil
            Route::post('{projetId}/confirmer-resultats-evaluation-note-conceptuelle/{noteId}', [NoteConceptuelleController::class, 'confirmerResultat']);

            // Routes pour les TDRs de préfaisabilité
            Route::post('{projetId}/soumettre-tdrs-prefaisabilite', [TdrPrefaisabiliteController::class, 'soumettreTdrs']);
            Route::post('{projetId}/apprecier-tdrs-prefaisabilite', [TdrPrefaisabiliteController::class, 'evaluerTdrs']);
            Route::get('{projetId}/details-appreciation-tdrs-prefaisabilite', [TdrPrefaisabiliteController::class, 'getEvaluationTdr']);
            Route::post('{projetId}/valider-tdrs-prefaisabilite', [TdrPrefaisabiliteController::class, 'validerTdrs']);
            Route::get('{projetId}/details-validations-tdrs-prefaisabilite', [TdrPrefaisabiliteController::class, 'getDetailsValidation']);
            Route::post('{projetId}/soumettre-rapport-prefaisabilite', [TdrPrefaisabiliteController::class, 'soumettreRapportPrefaisabilite']);
            Route::post('{projetId}/valider-etude-prefaisabilite', [TdrPrefaisabiliteController::class, 'validerEtudePrefaisabilite']);
            Route::post('{projetId}/soumettre-rapport-evaluation-ex-ante', [TdrPrefaisabiliteController::class, 'soumettreRapportEvaluationExAnte']);
            Route::post('{projetId}/valider-rapport-final', [TdrPrefaisabiliteController::class, 'validerRapportFinal']);

            // Routes pour les TDRs de faisabilité
            Route::post('{projetId}/soumettre-tdrs-faisabilite', [TdrFaisabiliteController::class, 'soumettreTdrs']);
            Route::post('{projetId}/apprecier-tdrs-faisabilite', [TdrFaisabiliteController::class, 'evaluerTdrs']);
            Route::get('{projetId}/details-appreciation-tdrs-faisabilite', [TdrFaisabiliteController::class, 'getEvaluationTdr']);
            Route::post('{projetId}/valider-tdrs-faisabilite', [TdrFaisabiliteController::class, 'validerTdrs']);
            Route::get('{projetId}/details-validations-tdrs-faisabilite', [TdrFaisabiliteController::class, 'getDetailsValidation']);
            Route::post('{projetId}/soumettre-rapport-faisabilite', [TdrFaisabiliteController::class, 'soumettreRapportFaisabilite']);
            Route::post('{projetId}/valider-etude-faisabilite', [TdrFaisabiliteController::class, 'validerEtudeFaisabilite']);
        });

        // Routes pour l'évaluation des notes conceptuelles
        Route::prefix('notes-conceptuelle')->group(function () {
            Route::get('{noteId}/evaluation-config', [NoteConceptuelleController::class, 'getWithEvaluationConfig']);
            Route::post('{noteId}/evaluation', [NoteConceptuelleController::class, 'creerEvaluation']);
            Route::get('{noteId}/evaluation', [NoteConceptuelleController::class, 'getEvaluation']);
            Route::put('evaluation/{evaluationId}', [NoteConceptuelleController::class, 'mettreAJourEvaluation']);
            Route::post('{noteId}/confirmer-resultats-evaluation', [NoteConceptuelleController::class, 'confirmerResultat']);
        });

        // Configuration des options de notation d'une note conceptuelle
        Route::get('grille-evaluation-note-conceptuelle', [NoteConceptuelleController::class, 'getOptionsNotationConfig']);
        Route::post('grille-evaluation-note-conceptuelle', [NoteConceptuelleController::class, 'configurerOptionsNotation']);

        Route::apiResource('categories-projet', CategorieProjetController::class);
        Route::apiResource('secteurs', SecteurController::class);

        Route::controller(SecteurController::class)->group(function () {
            Route::get('all-secteurs', 'all_secteurs');
            Route::get('grands-secteurs', 'grands_secteurs');
            Route::get('grands-secteurs/{id}/secteurs', 'secteurs_grand_secteur');
            Route::get('secteurs-seul', 'secteurs');
            Route::get('secteurs/{id}/sous-secteurs', 'sous_secteurs_secteur');
            Route::get('sous-secteurs', 'sous_secteurs');
        });

        // Project Configuration & Types
        Route::apiResource('cibles', CibleController::class);
        Route::apiResource('types-intervention', TypeInterventionController::class)
            ->parameters(['types-intervention' => 'type_intervention']);

        Route::apiResource('types-programme', TypeProgrammeController::class)
            ->parameters(['types-programme' => 'type_programme']);

        Route::prefix('programmes')->name('programmes.')->controller(TypeProgrammeController::class)->group(function () {
            Route::get("{id}/composants-programme", "composants_de_programme");
            Route::get("{idProgramme}/composants-programme/{idComposantProgramme}", "composants_composants_de_programme");
            Route::get("/", "programmes");
        });

        Route::controller(ComposantProgrammeController::class)->group(function () {
            Route::get('programmes/{idProgramme}/composants-programme/{idComposantProgramme}/composants', 'composants_de_programme');
        });

        Route::apiResource('composants-programme', ComposantProgrammeController::class)
            ->parameters(['composants-programme' => 'composant_programme']);

        Route::controller(ComposantProgrammeController::class)->group(function () {
            Route::get('programmes/composants-programme/{id}/composants', 'composants_de_programme');
        });

        Route::controller(ComposantProgrammeController::class)->group(function () {
            Route::get('composants-programme/{id}', 'composants_de_programme');
            Route::get('axes-pag', 'axesPag');
            Route::get('piliers-pag', 'piliersPag');
            Route::get('actions-pag', 'actionsPag');
            Route::get('orientations-strategiques-pnd', 'orientationsStrategiquesPnd');
            Route::get('objectifs-strategiques-pnd', 'objectifsStrategiquesPnd');
            Route::get('resultats-strategiques-pnd', 'resultatsStrategiquesPnd');
        });

        // Document Management
        Route::apiResource('documents', DocumentController::class);

        Route::prefix('fiches-idee')->group(function () {
            // Public routes
            Route::get('', [DocumentController::class, 'ficheIdee']);
            Route::post('/create-or-update', [DocumentController::class, 'createOrUpdateFicheIdee']);
        });

        Route::prefix('canevas-de-redaction-note-conceptuelle')->group(function () {
            // Public routes
            Route::get('', [DocumentController::class, 'canevasRedactionNoteConceptuelle']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasRedactionNoteConceptuelle']);
        });

        Route::prefix('canevas-appreciation-tdr')->group(function () {
            Route::get('', [DocumentController::class, 'canevasAppreciationTdr']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasAppreciationTdr']);
        });

        Route::prefix('canevas-checklist-suivi-rapport-prefaisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistSuiviRapportPrefaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistSuiviRapportPrefaisabilite']);
        });

        Route::prefix('canevas-checklist-mesures-adaptation')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistMesuresAdaptation']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistMesuresAdaptation']);
        });

        //ChecklistEtudeFaisabiliteMarche
        Route::prefix('canevas-checklist-etude-faisabilite-marche')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistEtudeFaisabiliteMarche']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistEtudeFaisabiliteMarche']);
        });

        Route::prefix('canevas-checklist-etude-faisabilite-technique')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistEtudeFaisabiliteTechnique']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistEtudeFaisabiliteTechnique']);
        });

        Route::prefix('canevas-checklist-etude-faisabilite-economique')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistEtudeFaisabiliteEconomique']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistEtudeFaisabiliteEconomique']);
        });

        Route::prefix('canevas-checklist-analyse-faisabilite-financiere')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistEtudeFaisabiliteFinanciere']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistEtudeFaisabiliteFinanciere']);
        });

        Route::prefix('canevas-checklist-etude-faisabilite-organisationnelle-juridique')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistEtudeFaisabiliteOrganisationnelleEtJuridique']);
        });

        Route::prefix('canevas-checklist-etude-faisabilite-environnemental-sociale')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistEtudeAnalyseImpactEnvironnementalEtSociale']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistEtudeAnalyseImpactEnvironnementalEtSociale']);
        });

        Route::prefix('canevas-checklist-suivi-assurance-qualite-etude-faisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistSuiviAssuranceQualiteEtudeFaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistSuiviAssuranceQualiteEtudeFaisabilite']);
        });

        Route::prefix('canevas-tdr-prefaisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasRedactionTdrPrefaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasRedactionTdrPrefaisabilite']);
        });
        Route::post('configurer-checklist-tdr-prefaisabilite', [DocumentController::class, 'configurerChecklistTdrPrefaisabilite']);

        Route::prefix('canevas-tdr-faisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasRedactionTdrFaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasRedactionTdrFaisabilite']);
        });

        Route::post('configurer-checklist-tdr-faisabilite', [DocumentController::class, 'configurerChecklistTdrFaisabilite']);

        Route::apiResource('categories-document', CategorieDocumentController::class);

        // Workflow & Process Management
        Route::apiResource('workflows', WorkflowController::class);

        // Financial & Evaluation
        Route::controller(FinancementController::class)->group(function () {
            Route::get('types-financement', 'types_de_financement');
            Route::get('types-financement/{idType}/natures', 'natures_type_de_financement');
            Route::get('natures-financement', 'natures_de_financement');
            Route::get('sources-financement', 'sources_de_financement');
            Route::get('natures-financement/{idNature}/sources', 'sources_nature_de_financement');
            Route::get('financements/filters', 'financementsWithFilters');
        });

        Route::apiResource('financements', FinancementController::class);

        Route::apiResource('evaluations', EvaluationController::class);
        Route::apiResource('champs', ChampController::class);

        // Routes spécifiques pour les évaluations
        Route::prefix('evaluations')->group(function () {
            Route::post('with-evaluateurs', [EvaluationController::class, 'createWithEvaluateurs'])
                ->name('evaluations.create-with-evaluateurs');
            Route::post('{id}/assign-evaluateurs', [EvaluationController::class, 'assignEvaluateurs'])
                ->name('evaluations.assign-evaluateurs');
            Route::get('{id}/progress', [EvaluationController::class, 'progress'])
                ->name('evaluations.progress');
            Route::post('{id}/finalize', [EvaluationController::class, 'finalize'])
                ->name('evaluations.finalize');
            Route::get('{id}/evaluateurs', [EvaluationController::class, 'evaluateurs'])
                ->name('evaluations.evaluateurs');
        });

        // Routes pour l'évaluation climatique unique des idées de projet
        Route::prefix('idees-projet/{ideeProjetId}/evaluation-climatique')->group(function () {
            Route::post('/', [EvaluationController::class, 'soumettreEvaluationClimatique'])
                ->name('idees-projet.evaluation-climatique.create');
            Route::get('/', [EvaluationController::class, 'getDashboardEvaluationClimatique'])
                ->name('idees-projet.evaluation-climatique.show');
            Route::get('/{evaluationId}/progress', [EvaluationController::class, 'updateClimaticEvaluationForIdee'])
                ->name('idees-projet.evaluation-climatique.progress');
            Route::post('/valider-score', [EvaluationController::class, 'finalizeEvaluation'])
                ->name('idees-projet.evaluation-climatique.finalize');
            Route::post('/reevaluer', [EvaluationController::class, 'refaireAutoEvaluationClimatique'])
                ->name('idees-projet.evaluation-climatique.reevaluer');

            // Routes pour soumettre les réponses
            Route::post('/soumettre', [EvaluationController::class, 'soumettreEvaluationClimatique'])
                ->name('idees-projet.evaluation-climatique.soumettre');
        });

        // Routes pour l'évaluation climatique unique des idées de projet
        Route::prefix('idees-projet/{ideeProjetId}')->group(function () {
            Route::post('validation', [EvaluationController::class, 'validerIdeeDeProjet'])
                ->name('idees-projet.validation');
            Route::get('decisions-validation-climatique', [EvaluationController::class, 'getDecisionsValiderIdeeDeProjet'])
                ->name('idees-projet.decisions-validation');
            Route::post('/validation-en-projet', [EvaluationController::class, 'validationIdeeDeProjetAProjet'])
                ->name('idees-projet.validation-en-projet');
            Route::get('decisions-validation-amc', [EvaluationController::class, 'getDecisionsValidationIdeeDeProjetAProjet'])
                ->name('idees-projet.decisions-validation-finale');
        });

        // Routes pour AMC des idées de projet
        Route::prefix('idees-projet/{ideeProjetId}/analyse-multi-critere')->group(function () {
            Route::post('/', [EvaluationController::class, 'appliquerAMC'])
                ->name('idees-projet.analyse-multi-critere');
            Route::get('/', [EvaluationController::class, 'getDashboardAMC'])
                ->name('idees-projet.analyse-multi-critere.show');
        });

        // Evaluation Criteria Management
        Route::apiResource('categories-critere', \App\Http\Controllers\CategorieCritereController::class)
            ->parameters(['categories-critere' => 'categorie_critere']);

        // Grille Evaluation Preliminaire des Impacts Climatique (Routes spécifiques)
        Route::prefix('grille-evaluation-preliminaire')->group(function () {
            Route::get('/', [\App\Http\Controllers\CategorieCritereController::class, 'getGrilleEvaluationPreliminaire'])
                ->name('grille-evaluation-preliminaire.get');
            Route::post('/', [\App\Http\Controllers\CategorieCritereController::class, 'updateGrilleEvaluationPreliminaire'])
                ->name('grille-evaluation-preliminaire.update');
        });

        // Grille d'analyse multicritere
        Route::prefix('grille-analyse-multi-critere')->group(function () {
            Route::get('/', [\App\Http\Controllers\CategorieCritereController::class, 'getGrilleAnalyseMultiCriteres'])
                ->name('grille-analyse-multi-critere.get');
            Route::post('/', [\App\Http\Controllers\CategorieCritereController::class, 'updateGrilleAnalyseMultiCriteres'])
                ->name('grille-analyse-multi-critere.update');
        });

        // Checklist des mesures d'adaptation pour projets à haut risque
        Route::get('checklist-mesures-adaptation', [\App\Http\Controllers\CategorieCritereController::class, 'getChecklistMesuresAdaptation'])
            ->name('checklist-mesures-adaptation.get');
        Route::post('checklist-mesures-adaptation', [\App\Http\Controllers\CategorieCritereController::class, 'createOrUpdateChecklistMesuresAdaptation'])
            ->name('checklist-mesures-adaptation.create-or-update');

        // SDG Integration
        Route::apiResource('odds', OddController::class);
    });


    // =============================================================================
    // ENUM ROUTES - For Frontend Dropdown Options
    // =============================================================================

    Route::prefix('enums')->group(function () {
        // Project Status & Workflow Enums
        Route::get('/statut-idee', function () {
            return [
                'statut'        => "success",
                'message'       => "Liste des statuts d'idee de projet",
                'data'          => \App\Enums\StatutIdee::options(),
                'statutCode'    => 200
            ];
        });

        Route::get('/phases-idee', function () {
            return [
                'statut'        => "success",
                'message'       => "Liste des phases d'idee de projet",
                'data'          => \App\Enums\PhasesIdee::options(),
                'statutCode'    => 200
            ];
        });

        Route::get('/sous-phase-idee', function () {
            return [
                'statut'        => "success",
                'message'       => "Liste des sous d'idee de projet",
                'data'          => \App\Enums\SousPhaseIdee::options(),
                'statutCode'    => 200
            ];
        });

        // Project composants & Configuration
        Route::get('/composants-projet', function () {
            return response()->json(\App\Enums\TypesProjet::options());
        });

        Route::get('/types-canevas', function () {
            return response()->json(\App\Enums\TypesCanevas::options());
        });

        Route::get('/types-template', function () {
            return response()->json(\App\Enums\TypesTemplate::options());
        });

        // Organization Types
        Route::get('/types-organisation', function () {
            return response()->json(\App\Enums\EnumTypeOrganisation::options());
        });

        // All enums endpoint for bulk loading
        Route::get('/all', function () {
            return [
                'statut'        => "success",
                'message'       => "Liste des sous d'idee de projet",
                'data'          => [
                    'statut_idee' => \App\Enums\StatutIdee::options(),
                    'phases_idee' => \App\Enums\PhasesIdee::options(),
                    'sous_phase_idee' => \App\Enums\SousPhaseIdee::options(),
                    'types_projet' => \App\Enums\TypesProjet::options(),
                    'types_canevas' => \App\Enums\TypesCanevas::options(),
                    'types_template' => \App\Enums\TypesTemplate::options(),
                    'types_organisation' => \App\Enums\EnumTypeOrganisation::options(),
                ],
                'statutCode'    => 200
            ];
        });
    });
});
// =============================================================================
// AUTHENTICATION ROUTES (Keycloak)
// =============================================================================

Route::prefix('keycloak-auths')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    // Protected routes with Keycloak middleware
    Route::middleware('keycloak')->group(function () {
        Route::get('/profile', [AuthController::class, 'profile']);
        Route::put('/profile', [AuthController::class, 'updateProfile']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/introspect', [AuthController::class, 'introspect']);


        /*
            // Routes spécifiques aux rôles
            Route::middleware(['role:DPAF'])->prefix('projets')->group(function () {
                Route::get('/saisie-fiche', [ProjetController::class, 'createFiche'])
                    ->name('projets.create-fiche');
            });

            Route::middleware(['role:DGPD'])->prefix('analyse')->group(function () {
                Route::get('/multi-criteres', [AnalyseController::class, 'multiCriteres'])
                    ->name('analyse.multi-criteres');
            });
        */
    });
});

Route::get('/test-json', function () {
    $json = file_get_contents(public_path('decoupage_territorial_benin.json'));
    $data = json_decode($json, true);

    $arrondissements = collect($data)
        ->pluck('communes')
        ->flatten(1)
        ->pluck('arrondissements')
        ->flatten(1);

    //DB::table("villages")->truncate();

    // Générer tous les arrondissements basés sur les données du CommuneSeeder
    foreach ($arrondissements as $arrondissement) {
        // Récupérer l'ID de la commune
        $arrondissementRecord = DB::table('arrondissements')->where('slug', Str::slug($arrondissement['lib_arrond']))->first();

        if ($arrondissementRecord && isset($arrondissement['quartiers'])) {
            foreach ($arrondissement['quartiers'] as $index => $quartier) {

                $code = $arrondissementRecord->code . '-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                $slug = Str::slug($quartier["lib_quart"]);

                /*$count = DB::table('villages')->where('slug', $slug)->count();

                if ($count) {
                    $slug .= $count;
                }*/

                Village::updateOrCreate([
                    'code' => $code,
                    'slug' => $slug,
                    'arrondissementId' => $arrondissementRecord->id
                ], [

                    'code' => $code,
                    'nom' => Str::title($quartier["lib_quart"]),
                    'slug' => $slug,
                    'arrondissementId' => $arrondissementRecord->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                /*DB::table('villages')->insert([
                    'code' => $code,
                    'nom' => Str::title($quartier["lib_quart"]),
                    'slug' => $slug,
                    'arrondissementId' => $arrondissementRecord->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);*/
            }
        }
    }



    return response()->json(Village::all());
});
