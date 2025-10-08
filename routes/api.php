<?php

use Illuminate\Support\Facades\Route;

// Import all controllers
use App\Http\Controllers\ArrondissementController;
use App\Http\Controllers\CategorieDocumentController;
use App\Http\Controllers\CategorieProjetController;
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
use App\Http\Controllers\CommentaireController;
use App\Http\Controllers\DgpdController;
use App\Http\Controllers\DpafController;
use App\Http\Controllers\FichierController;
use App\Http\Controllers\GroupeUtilisateurController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\NoteConceptuelleController;
use App\Http\Controllers\TdrPrefaisabiliteController;
use App\Http\Controllers\TdrFaisabiliteController;
use App\Http\Controllers\PassportClientController;
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
            //Route::get('{projetId}/details-validation-note-conceptuelle/{noteId}', [NoteConceptuelleController::class, 'getValidationDetails']);
            // Route pour la validation à l'étape étude de profil
            Route::get('{projetId}/details-etude-profil', [NoteConceptuelleController::class, 'getDetailsEtudeProfil']);
            Route::post('{projetId}/valider-etude-profil', [NoteConceptuelleController::class, 'validerEtudeProfil']);
            // Route pour la validation à l'étape étude de profil
            Route::post('{projetId}/confirmer-resultats-evaluation-note-conceptuelle/{noteId}', [NoteConceptuelleController::class, 'confirmerResultat']);

            // Routes pour les TDRs de préfaisabilité
            Route::get('{projetId}/details-tdr-prefaisabilite', [TdrPrefaisabiliteController::class, 'getTdrDetails']);
            Route::post('{projetId}/soumettre-tdrs-prefaisabilite', [TdrPrefaisabiliteController::class, 'soumettreTdrs']);
            Route::post('{projetId}/apprecier-tdrs-prefaisabilite', [TdrPrefaisabiliteController::class, 'evaluerTdrs']);
            Route::get('{projetId}/details-appreciation-tdrs-prefaisabilite', [TdrPrefaisabiliteController::class, 'getEvaluationTdr']);
            Route::post('{projetId}/valider-tdrs-prefaisabilite', [TdrPrefaisabiliteController::class, 'validerTdrs']);
            Route::post('{projetId}/soumettre-rapport-prefaisabilite', [TdrPrefaisabiliteController::class, 'soumettreRapportPrefaisabilite']);
            Route::get('{projetId}/details-soumission-rapport-prefaisabilite', [TdrPrefaisabiliteController::class, 'getDetailsSoumissionRapportPrefaisabilite']);
            Route::post('{projetId}/valider-etude-prefaisabilite', [TdrPrefaisabiliteController::class, 'validerEtudePrefaisabilite']);
            Route::get('{projetId}/details-validations-etude-prefaisabilite', [TdrPrefaisabiliteController::class, 'getDetailsValidationEtude']);
            Route::post('{projetId}/soumettre-rapport-evaluation-ex-ante', [TdrPrefaisabiliteController::class, 'soumettreRapportEvaluationExAnte']);
            Route::get('{projetId}/details-soumission-rapport-final', [TdrPrefaisabiliteController::class, 'getDetailsSoumissionRapportFinale']);
            Route::post('{projetId}/valider-rapport-final', [TdrPrefaisabiliteController::class, 'validerRapportFinal']);
            Route::get('{projetId}/details-validation-final', [TdrPrefaisabiliteController::class, 'getDetailsValidationFinal']);

            // Routes pour les TDRs de faisabilité
            Route::get('{projetId}/details-tdr-faisabilite', [TdrFaisabiliteController::class, 'getTdrDetails']);
            Route::post('{projetId}/soumettre-tdrs-faisabilite', [TdrFaisabiliteController::class, 'soumettreTdrs']);
            Route::post('{projetId}/apprecier-tdrs-faisabilite', [TdrFaisabiliteController::class, 'evaluerTdrs']);
            Route::get('{projetId}/details-appreciation-tdrs-faisabilite', [TdrFaisabiliteController::class, 'getEvaluationTdr']);
            Route::post('{projetId}/valider-tdrs-faisabilite', [TdrFaisabiliteController::class, 'validerTdrs']);
            Route::get('{projetId}/details-validations-tdrs-faisabilite', [TdrFaisabiliteController::class, 'getDetailsValidationEtude']);
            Route::post('{projetId}/soumettre-rapport-faisabilite', [TdrFaisabiliteController::class, 'soumettreRapportFaisabilite']);
            Route::get('{projetId}/details-soumission-rapport-faisabilite', [TdrFaisabiliteController::class, 'getDetailsSoumissionRapportFaisabilite']);
            Route::post('{projetId}/valider-etude-faisabilite', [TdrFaisabiliteController::class, 'validerEtudeFaisabilite']);
            Route::get('{projetId}/details-validations-etude-faisabilite', [TdrFaisabiliteController::class, 'getDetailsValidationEtude']);
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

        Route::apiResource('categories-projet', CategorieProjetController::class)
            ->parameters(['categories-projet' => 'categorie_projet']);
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

        Route::prefix('canevas-appreciation-note-conceptuelle')->group(function () {
            // Public routes
            Route::get('', [DocumentController::class, 'canevasAppreciationNoteConceptuelle']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasAppreciationNoteConceptuelle']);
        });

        Route::prefix('canevas-appreciation-tdr')->group(function () {
            Route::get('', [DocumentController::class, 'canevasAppreciationTdr']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasAppreciationTdr']);
        });

        Route::prefix('canevas-appreciation-tdr-prefaisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasAppreciationTdrPrefaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasAppreciationTdrPrefaisabilite']);
        });

        Route::prefix('canevas-appreciation-tdr-faisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasAppreciationTdrFaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasAppreciationTdrFaisabilite']);
        });

        Route::prefix('canevas-checklist-suivi-rapport-prefaisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistSuiviRapportPrefaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistSuiviRapportPrefaisabilite']);
        });

        /* Route::prefix('canevas-check-liste-mesures-adaptation')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklistMesuresAdaptation']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklistMesuresAdaptation']);
        }); */

        //ChecklistEtudeFaisabiliteMarche
        Route::prefix('canevas-check-liste-etude-faisabilite-marche')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklisteEtudeFaisabiliteMarche']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklisteEtudeFaisabiliteMarche']);
        });

        Route::prefix('canevas-check-liste-etude-faisabilite-technique')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklisteEtudeFaisabiliteTechnique']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklisteEtudeFaisabiliteTechnique']);
        });

        Route::prefix('canevas-check-liste-etude-faisabilite-economique')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklisteEtudeFaisabiliteEconomique']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklisteEtudeFaisabiliteEconomique']);
        });

        Route::prefix('canevas-check-liste-de-suivi-analyse-de-faisabilite-financiere')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklisteSuiviAnalyseDeFaisabiliteFinanciere']);
        });

        Route::prefix('canevas-check-liste-de-suivi-etude-de-faisabilite-organisationnelle-juridique')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklisteEtudeFaisabiliteOrganisationnelleEtJuridique']);
        });

        Route::prefix('canevas-check-liste-de-suivi-etude-analyse-impact-environnementale-sociale')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklisteSuiviEtudeAnalyseImpactEnvironnementaleEtSociale']);
        });

        Route::prefix('canevas-check-liste-suivi-assurance-qualite-rapport-etude-faisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklisteSuiviAssuranceQualiteRapportEtudeFaisabilite']);
        });

        Route::get('canevas-check-listes-suivi-rapport-etude-faisabilite', [DocumentController::class, 'canevasChecklistesSuiviRapportEtudeFaisabilite']);


        /* Route::prefix('canevas-tdr-prefaisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasRedactionTdrPrefaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasRedactionTdrPrefaisabilite']);
        });
        Route::post('configurer-checklist-tdr-prefaisabilite', [DocumentController::class, 'configurerChecklistTdrPrefaisabilite']);
        */
        /* Route::prefix('canevas-tdr-faisabilite')->group(function () {
            Route::get('', [DocumentController::class, 'canevasRedactionTdrFaisabilite']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasRedactionTdrFaisabilite']);
        });

        Route::post('configurer-checklist-tdr-faisabilite', [DocumentController::class, 'configurerChecklistTdrFaisabilite']); */

        Route::apiResource('categories-document', CategorieDocumentController::class)
            ->parameters(['categories-document' => 'categorie_document']);

        // Workflow & Process Management
        Route::apiResource('workflows', WorkflowController::class);

        // Commentaire
        Route::apiResource('commentaires', CommentaireController::class);

        // Fichier - Routes personnalisées AVANT la resource
        Route::get('visualiserFichier/{hash}', [FichierController::class, 'visualiserFichierParHash'])->name('fichiers.view');
        Route::get('telechargerFichier/{hash}', [FichierController::class, 'telechargerFichierParHash'])->name('fichiers.download');

        // Fichier - Routes resource
        Route::apiResource('fichiers', FichierController::class);

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

        // Routes pour l'évaluation de pertinence des idées de projet
        /*Route::prefix('idees-projet/{ideeProjetId}/evaluation-pertinence')->group(function () {
            Route::post('/', [EvaluationController::class, 'soumettreEvaluationPertinence'])
                ->name('idees-projet.evaluation-pertinence.create');
            Route::get('/dashboard', [EvaluationController::class, 'getDashboardEvaluationPertinence'])
                ->name('idees-projet.evaluation-pertinence.dashboard');
            Route::post('/refaire', [EvaluationController::class, 'refaireAutoEvaluationPertinence'])
                ->name('idees-projet.evaluation-pertinence.refaire');
        });

        // Route pour obtenir le score de pertinence
        Route::get('evaluations/{evaluationId}/pertinence/score', [EvaluationController::class, 'getScorePertinence'])
            ->name('evaluations.pertinence.score');*/

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

        // Grille d'évaluation de pertinence
        /*Route::prefix('grille-evaluation-pertinence')->group(function () {
            Route::get('/', [\App\Http\Controllers\CategorieCritereController::class, 'getGrilleEvaluationPertinence'])
                ->name('grille-evaluation-pertinence.get');
            Route::post('/', [\App\Http\Controllers\CategorieCritereController::class, 'updateGrilleEvaluationPertinence'])
                ->name('grille-evaluation-pertinence.update');
            Route::get('/{idee_projet_id}', [\App\Http\Controllers\CategorieCritereController::class, 'getGrilleEvaluationPertinenceAvecEvaluations'])
                ->name('grille-evaluation-pertinence.get-avec-evaluations')
                ->where('idee_projet_id', '[0-9]+');
        });*/

        // Checklist des mesures d'adaptation pour projets à haut risque
        Route::get('checklist-mesures-adaptation/{id}/secteur', [\App\Http\Controllers\CategorieCritereController::class, 'getChecklistMesuresAdaptationSecteur'])
            ->name('checklist-mesures-adaptation.get');
        Route::get('checklist-mesures-adaptation', [\App\Http\Controllers\CategorieCritereController::class, 'getChecklistMesuresAdaptation'])
            ->name('checklist-mesures-adaptation.get');
        Route::post('checklist-mesures-adaptation', [\App\Http\Controllers\CategorieCritereController::class, 'createOrUpdateChecklistMesuresAdaptation'])
            ->name('checklist-mesures-adaptation.create-or-update');

        // SDG Integration
        Route::apiResource('odds', OddController::class);

        // =============================================================================
        // GESTION DES CLIENTS OAUTH PASSPORT
        // =============================================================================

        Route::prefix('oauth/clients')->middleware(['oauth.audit'])->group(function () {
            Route::get('/', [PassportClientController::class, 'index'])->name('oauth.clients.index');
            Route::post('/', [PassportClientController::class, 'store'])->name('oauth.clients.store');

            // Routes spécifiques par type de client - CREATE
            Route::post('/client-credentials', [PassportClientController::class, 'storeClientCredentials'])->name('oauth.clients.client-credentials.store');
            Route::post('/personal-access', [PassportClientController::class, 'storePersonalAccessClient'])->name('oauth.clients.personal-access.store');
            Route::post('/password-grant', [PassportClientController::class, 'storePasswordClient'])->name('oauth.clients.password-grant.store');
            Route::post('/authorization-code', [PassportClientController::class, 'storeAuthorizationCodeClient'])->name('oauth.clients.authorization-code.store');

            // Routes spécifiques par type de client - GET
            Route::get('/client-credentials', [PassportClientController::class, 'indexClientCredentials'])->name('oauth.clients.client-credentials.index');
            Route::get('/personal-access', [PassportClientController::class, 'indexPersonalAccessClients'])->name('oauth.clients.personal-access.index');
            Route::get('/password-grant', [PassportClientController::class, 'indexPasswordClients'])->name('oauth.clients.password-grant.index');
            Route::get('/authorization-code', [PassportClientController::class, 'indexAuthorizationCodeClients'])->name('oauth.clients.authorization-code.index');

            // Routes spécifiques par type de client - Get
            Route::get('/client-credentials/{id}', [PassportClientController::class, 'findClientCredentials'])->name('oauth.clients.client-credentials.find');
            Route::get('/personal-access/{id}', [PassportClientController::class, 'findPersonalAccessClient'])->name('oauth.clients.personal-access.find');
            Route::get('/password-grant/{id}', [PassportClientController::class, 'findPasswordClient'])->name('oauth.clients.password-grant.find');
            Route::get('/authorization-code/{id}', [PassportClientController::class, 'findAuthorizationCodeClient'])->name('oauth.clients.authorization-code.find');

            // Routes spécifiques par type de client - UPDATE
            Route::put('/client-credentials/{id}', [PassportClientController::class, 'updateClientCredentials'])->name('oauth.clients.client-credentials.update');
            Route::put('/personal-access/{id}', [PassportClientController::class, 'updatePersonalAccessClient'])->name('oauth.clients.personal-access.update');
            Route::put('/password-grant/{id}', [PassportClientController::class, 'updatePasswordClient'])->name('oauth.clients.password-grant.update');
            Route::put('/authorization-code/{id}', [PassportClientController::class, 'updateAuthorizationCodeClient'])->name('oauth.clients.authorization-code.update');

            Route::get('/stats', [PassportClientController::class, 'stats'])->name('oauth.clients.stats');
            Route::get('/search', [PassportClientController::class, 'search'])->name('oauth.clients.search');
            Route::get('/scopes', [PassportClientController::class, 'availableScopes'])->name('oauth.clients.scopes');

            // Routes de sécurité et maintenance
            Route::post('/security/rotate-expired-secrets', [PassportClientController::class, 'rotateExpiredSecrets'])->name('oauth.clients.security.rotate-expired');
            Route::post('/security/cleanup-tokens', [PassportClientController::class, 'cleanupTokens'])->name('oauth.clients.security.cleanup');
            Route::get('/security/audit', [PassportClientController::class, 'auditAccess'])->name('oauth.clients.security.audit');

            // Routes d'expiration et rafraîchissement des tokens
            Route::post('/tokens/check-expired', [PassportClientController::class, 'checkExpiredTokens'])->name('oauth.clients.tokens.check-expired');
            Route::post('/tokens/refresh', [PassportClientController::class, 'refreshToken'])->name('oauth.clients.tokens.refresh');
            Route::get('/tokens/{tokenId}/expiration', [PassportClientController::class, 'getTokenExpiration'])->name('oauth.clients.tokens.expiration');
            Route::post('/tokens/configure-expiration', [PassportClientController::class, 'configureExpiration'])->name('oauth.clients.tokens.configure-expiration');
            Route::get('/tokens/expiration-stats', [PassportClientController::class, 'expirationStats'])->name('oauth.clients.tokens.expiration-stats');

            Route::get('/{id}', [PassportClientController::class, 'show'])->name('oauth.clients.show');
            Route::put('/{id}', [PassportClientController::class, 'update'])->name('oauth.clients.update');
            Route::delete('/{id}', [PassportClientController::class, 'destroy'])->name('oauth.clients.destroy');
            Route::post('/{id}/regenerate-secret', [PassportClientController::class, 'regenerateSecret'])->name('oauth.clients.regenerate-secret');
            Route::post('/{id}/force-rotate-secret', [PassportClientController::class, 'forceRotateSecret'])->name('oauth.clients.force-rotate-secret');
            Route::post('/{id}/restore', [PassportClientController::class, 'restore'])->name('oauth.clients.restore');
            Route::delete('/{id}/force-delete', [PassportClientController::class, 'forceDelete'])->name('oauth.clients.force-delete');
            Route::get('/{id}/tokens', [PassportClientController::class, 'activeTokens'])->name('oauth.clients.tokens');
            Route::post('/{id}/revoke-tokens', [PassportClientController::class, 'revokeTokens'])->name('oauth.clients.revoke-tokens');
        });
    });

    require __DIR__ . '/integration_bip.php';


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

    Route::get("callback", function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Callback route is working!'
        ]);
    })->name('callback.test');

    //Route::get("callback", [PassportClientController::class, 'handleProviderCallback'])->name('ad.oauth.callback');

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

Route::get('/update-villages', function () {

    //return Village::all()->each->update(["longitude"=>2.899612094352824, "latitude" => 11.011495856466121]);
});

Route::get('/traitement-arrondissements', function () {

    // 1. Charger le contenu du fichier GeoJSON
    // Assurez-vous que ce chemin est correct pour votre fichier d'arrondissement
    $arrondissement_geojson_content = file_get_contents(public_path('geodata/arrondissement.geojson'));
    //$geojson_content = file_get_contents('arrondissement.geojson');

    // Vérifier si le fichier a été lu
    if ($arrondissement_geojson_content === false) {
        die("Erreur : Impossible de lire le fichier GeoJSON des arrondissements.");
    }

    // 2. Décoder le GeoJSON en un objet/tableau PHP
    $arrondissement_data = json_decode($arrondissement_geojson_content, true);

    if ($arrondissement_data === null || !isset($arrondissement_data['features'])) {
        die("Erreur : Le fichier GeoJSON des arrondissements est invalide.");
    }
    $arrondissement_lookup = [];
    // Clés à adapter :
    $ARR_NAME_KEY = 'NOM_ARRONDISSEMENT'; // Clé qui contient le nom de l'arrondissement dans ce fichier
    $GEOMETRY_FIELD = 'geometry';         // Clé contenant la géométrie (Polygon/MultiPolygon)


    // Vérifier si le décodage a réussi
    if ($data === null || !isset($data['features'])) {
        die("Erreur : Le fichier GeoJSON est invalide ou ne contient pas de 'features'.");
    }

    // Initialiser la structure finale
    $structure_administrative = [];
});

Route::get('/traitement-villages', function () {

    // 1. Charger le contenu du fichier GeoJSON
    // NOTE : Remplacez 'data_chef_lieu_village.geojson' par le chemin réel du fichier.
    $geojson_content = file_get_contents(public_path('geodata/data_chef_lieu_village.geojson'));
    //$geojson_content = file_get_contents('data_chef_lieu_village.geojson');

    // Vérifier si le fichier a été lu
    if ($geojson_content === false) {
        die("Erreur : Impossible de lire le fichier GeoJSON.");
    }

    // 2. Décoder le GeoJSON en un objet/tableau PHP
    $data = json_decode($geojson_content, true);

    // Vérifier si le décodage a réussi
    if ($data === null || !isset($data['features'])) {
        die("Erreur : Le fichier GeoJSON est invalide ou ne contient pas de 'features'.");
    }

    // Initialiser la structure finale
    $structure_administrative = [];

    // 3. Boucler sur toutes les entités (villages/quartiers)
    foreach ($data['features'] as $feature) {
        // Récupérer les propriétés (attributs) de l'entité
        $properties = $feature['properties'];

        // Extraire les noms des niveaux administratifs
        // Les clés utilisées sont basées sur l'analyse de votre fichier GeoJSON
        $departement        = $properties['Départeme'] ?? 'INCONNU';
        $commune            = $properties['Commune'] ?? 'INCONNU';
        $arrondissement     = $properties['Arrondisst'] ?? 'INCONNU';
        $villageName        = $properties['Nom_LOC'] ?? 'Village sans nom';
        $village            = $properties['Village_Ad'] ?? 'Village sans nom';

        // Extraction des coordonnées du village (Longitude [0], Latitude [1])
        $coordinates = $feature['geometry']['coordinates'] ?? [null, null];

        // --- 4. CONSTRUCTION HIÉRARCHIQUE (Imbrication par référence) ---

        // 4.1. Traiter le Département
        if (!isset($structure_administrative[$departement])) {
            // Enregistrer les coordonnées du premier village rencontré comme point de référence du département
            $first_point_dep[$departement] = $coordinates;

            // Créer l'entrée du département et initialiser sa sous-structure 'Communes'
            $structure_administrative[$departement] = [
                "coordinates" => $coordinates, // Long, Lat du point de référence
                "communes" => []
            ];
        }

        // Pointer vers la sous-structure 'Communes' du département
        $communes_ref = &$structure_administrative[$departement]["communes"];

        // 4.2. Traiter la Commune
        if (!isset($communes_ref[$commune])) {
            // Enregistrer les coordonnées du premier village rencontré comme point de référence de la commune
            $first_point_com[$commune] = $coordinates;

            // Créer l'entrée de la commune et initialiser sa sous-structure 'Arrondissements'
            $communes_ref[$commune] = [
                "coordinates" => $coordinates, // Long, Lat du point de référence
                "arrondissements" => []
            ];
        }

        // Pointer vers la sous-structure 'Arrondissements' de la commune
        $arrondissements_ref = &$communes_ref[$commune]["arrondissements"];

        // 4.3. Traiter l'Arrondissement
        if (!isset($arrondissements_ref[$arrondissement])) {
            // Enregistrer les coordonnées du premier village rencontré comme point de référence de l'arrondissement
            $first_point_arr[$arrondissement] = $coordinates;
            // Créer l'entrée de l'arrondissement et initialiser sa liste de 'villages'
            $arrondissements_ref[$arrondissement] = [
                "coordinates" => $coordinates, // Long, Lat du point de référence
                "villages" => []
            ];
        }

        // 4.4. Ajouter le Village (seulement si non déjà présent pour éviter les doublons)
        $villages_ref = &$arrondissements_ref[$arrondissement]["villages"];
        // Le village est stocké comme un objet/tableau, non une simple chaîne
        $village_data = [
            "nom" => $villageName,
            "code" => $village,
            "Coordinates" => $coordinates // Long, Lat exactes du village
        ];


        // Vérifier si le village (par son nom) est déjà dans la liste avant d'ajouter
        $exists = false;
        foreach ($villages_ref as $v) {
            if ($v["code"] === $village) {
                $exists = true;
                break;
            }
        }

        if (!$exists) {
            $villages_ref[] = $village_data;
        }
/*
        if (!in_array($village, $villages_ref)) {
            $villages_ref[] = $village;
        } */
    }

    return response()->json($structure_administrative);

    // 5. Afficher la structure finale au format JSON (pour une utilisation ultérieure)
    header('Content-Type: application/json');
    echo json_encode($structure_administrative, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

// Route de test pour Reverb
Route::get('/test-reverb', function () {
    try {
        // Test simple avec Broadcast::channel
        \Illuminate\Support\Facades\Broadcast::on('test-channel')
            ->as('test-event')
            ->with([
                'message' => 'Test Reverb depuis Laravel Backend!',
                'timestamp' => now()->toISOString(),
                'status' => 'success',
                'server_info' => [
                    'broadcast_driver' => config('broadcasting.default'),
                    'reverb_host' => config('broadcasting.connections.reverb.options.host'),
                    'reverb_port' => config('broadcasting.connections.reverb.options.port'),
                ]
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Event diffusé avec succès sur Reverb!',
            'channel' => 'test-channel',
            'event' => 'test-event',
            'time' => now()->toISOString(),
            'config' => [
                'broadcast_driver' => config('broadcasting.default'),
                'reverb_app_id' => config('broadcasting.connections.reverb.app_id'),
                'reverb_host' => config('broadcasting.connections.reverb.options.host'),
                'reverb_port' => config('broadcasting.connections.reverb.options.port'),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du broadcast: ' . $e->getMessage(),
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

// Route de test pour broadcaster vers un utilisateur spécifique
Route::get('/test-user-broadcast/{userId}', function ($userId) {
    try {
        // Vérifier si l'utilisateur existe
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Utilisateur non trouvé',
            ], 404);
        }

        // Broadcaster vers le canal privé de l'utilisateur
        \Illuminate\Support\Facades\Broadcast::on('private-user.' . $userId)
            ->as('user-notification')
            ->with([
                'message' => 'Message personnel pour ' . ($user->name ?? $user->email),
                'title' => 'Notification privée',
                'type' => 'info',
                'timestamp' => now()->toISOString(),
                'user_data' => [
                    'id' => $user->id,
                    'name' => $user->name ?? $user->email,
                    'email' => $user->email,
                ],
                'action_required' => false,
                'metadata' => [
                    'sender' => 'Laravel Backend Test',
                    'channel_type' => 'private',
                    'broadcast_driver' => config('broadcasting.default')
                ]
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification envoyée à l\'utilisateur!',
            'user' => [
                'id' => $user->id,
                'name' => $user->name ?? $user->email,
                'email' => $user->email,
            ],
            'channel' => 'private-user.' . $userId,
            'event' => 'user-notification',
            'time' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors du broadcast: ' . $e->getMessage(),
            'error' => $e->getMessage()
        ], 500);
    }
});

// Route pour envoyer une notification système existante
Route::post('/test-notification/{userId}', function ($userId, \Illuminate\Http\Request $request) {
    try {
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Utilisateur non trouvé'], 404);
        }

        // Utiliser le système de notification existant
        $notification = new \App\Notifications\NouvelleIdeeProjetNotification(
            // Créer une IdeeProjet factice pour le test ou utiliser une existante
            new \App\Models\IdeeProjet([
                'id' => 999,
                'sigle' => 'TEST-REVERB',
                'titre' => 'Test de notification Reverb',
            ]),
            85.5 // Score climatique de test
        );

        $user->notify($notification);

        return response()->json([
            'success' => true,
            'message' => 'Notification système envoyée!',
            'user_id' => $userId,
            'notification_type' => 'NouvelleIdeeProjetNotification',
            'channels' => ['database', 'broadcast', 'mail']
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur: ' . $e->getMessage(),
            'error' => $e->getMessage()
        ], 500);
    }
});


// Route pour broadcaster vers les abonnés d'une idée de projet
Route::get('/broadcast-to-idea/{ideeId}', function ($ideeId) {
    try {
        // Récupérer l'idée de projet
        $idee = \App\Models\IdeeProjet::find($ideeId);
        if (!$idee) {
            return response()->json([
                'success' => false,
                'message' => 'Idée de projet non trouvée',
            ], 404);
        }

        // Broadcaster vers le canal de l'idée
        \Illuminate\Support\Facades\Broadcast::on('idee.de.projet.creer.' . $ideeId)
            ->as('idee.update')
            ->with([
                'message' => 'Mise à jour sur l\'idée de projet: ' . $idee->sigle,
                'title' => 'Notification - ' . $idee->titre,
                'type' => 'info',
                'timestamp' => now()->toISOString(),
                'idee_data' => [
                    'id' => $idee->id,
                    'sigle' => $idee->sigle,
                    'titre' => $idee->titre,
                    'statut' => $idee->statut,
                ],
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification diffusée aux abonnés de l\'idée!',
            'channel' => 'idee.de.projet.creer.' . $ideeId,
            'event' => 'idee.update',
            'time' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

// Route pour broadcaster vers tous les membres d'un ministère
Route::get('/broadcast-to-ministere/{ministereId}', function ($ministereId) {
    try {
        // Récupérer le ministère (organisation parent)
        $ministere = \App\Models\Organisation::whereNull('parentId')->find($ministereId);
        if (!$ministere) {
            return response()->json([
                'success' => false,
                'message' => 'Ministère non trouvé',
            ], 404);
        }

        // Broadcaster vers le canal du ministère
        \Illuminate\Support\Facades\Broadcast::on('ministere.' . $ministereId)
            ->as('ministere.notification')
            ->with([
                'message' => 'Notification pour tous les membres du ministère: ' . $ministere->nom,
                'title' => 'Notification Ministère',
                'type' => 'info',
                'timestamp' => now()->toISOString(),
                'ministere_data' => [
                    'id' => $ministere->id,
                    'nom' => $ministere->nom,
                ],
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification diffusée à tous les membres du ministère!',
            'channel' => 'ministere.' . $ministereId,
            'event' => 'ministere.notification',
            'time' => now()->toISOString()
        ]);
    } catch (\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});
