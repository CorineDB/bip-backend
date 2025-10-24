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
use App\Http\Resources\auth\LoginResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

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

    // Route d'authentification pour le broadcasting (WebSocket)
    Broadcast::routes(['middleware' => ['auth:api']]);

    Route::group(['prefix' => 'passport-auths', 'as' => 'auths.'], function () {

        Route::post('authentification', [OAuthController::class, 'authentification'])->name('authentification');

        Route::get('confirmation-de-compte/{email}', [OAuthController::class, 'confirmationDeCompte'])->name('auths.confirmationDeCompte');

        Route::get('activation-de-compte/{token}', [OAuthController::class, 'activationDeCompte'])->name('activationDeCompte');

        Route::get('reinitialisation-de-mot-de-passe/{email}', [OAuthController::class, 'verificationEmailReinitialisationMotDePasse'])->name('verificationEmailReinitialisationMotDePasse');

        Route::get('verification-de-compte/{token}', [OAuthController::class, 'verificationDeCompte'])->name('verificationDeCompte');

        Route::post('reinitialisation-de-mot-de-passe', [OAuthController::class, 'reinitialisationDeMotDePasse'])->name('reinitialisationDeMotDePasse');

        // Callback Active Directory pour activation et authentification
        Route::get('ad-callback', [OAuthController::class, 'adCallback'])->name('adCallback');

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
    // API RESOURCEROUTES - Full CRUD Controllers (23 controllers)
    // =============================================================================


    Route::group(['middleware' => ['auth:api']], function () {
        // Geographic & Administrative Resources
        Route::apiResource('arrondissements', ArrondissementController::class)->only(['index', 'show'])
            ->parameters(['arrondissements' => 'arrondissementId']);
        Route::apiResource('communes', CommuneController::class)->only(['index', 'show'])
            ->parameters(['communes' => 'communeId']);
        Route::apiResource('departements', DepartementController::class)->only(['index', 'show'])
            ->parameters(['departements' => 'departementId']);
        Route::apiResource('villages', VillageController::class)->only(['index', 'show'])
            ->parameters(['villages' => 'villageId']);
        Route::post('villages/filter', [VillageController::class, 'filter']);

        Route::prefix('departements')->group(function () {
            Route::get('{departementId}/communes', [DepartementController::class, 'communes']);
        });

        Route::prefix('communes')->group(function () {
            Route::get('{communeId}/arrondissements', [CommuneController::class, 'arrondissements']);
        });

        Route::prefix('arrondissements')->group(function () {
            Route::get('{arrondissementId}/villages', [ArrondissementController::class, 'villages']);
        });

        // Organization & People Management
        Route::apiResource('organisations', OrganisationController::class);
        Route::apiResource('dpaf', DpafController::class)->except(['destroy']);
        Route::apiResource('dgpd', DgpdController::class)->except(['destroy']);

        Route::controller(OrganisationController::class)->group(function () {
            Route::get('ministeres', 'ministeres');
            Route::get('ministeres/{ministereId}/organismes_tutelle', 'organismes_tutelle');
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
            Route::get('/unread', [NotificationController::class, 'unread']);
            Route::get('/read', [NotificationController::class, 'read']);
            Route::get('/type/{type}', [NotificationController::class, 'byType']);
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::delete('/delete-all-read', [NotificationController::class, 'deleteAllRead']);
            Route::delete('/delete-all', [NotificationController::class, 'deleteAll']);
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

            // Route pour la validation à l'étape étude de profil
            Route::get('{projetId}/details-etude-profil', [NoteConceptuelleController::class, 'getDetailsEtudeProfil']);
            Route::post('{projetId}/valider-etude-profil', [NoteConceptuelleController::class, 'validerEtudeProfil']);
            // Route pour la soumission du rapport de faisabilité préliminaire
            Route::post('{projetId}/soumettre-rapport-faisabilite-preliminaire', [NoteConceptuelleController::class, 'soumettreRapportFaisabilitePreliminaire']);
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
            Route::get('grands-secteurs/{secteur}/secteurs', 'secteurs_grand_secteur');
            Route::get('secteurs-seul', 'secteurs');
            Route::get('secteurs/{secteur}/sous-secteurs', 'sous_secteurs_secteur');
            Route::get('sous-secteurs', 'sous_secteurs');
        });

        // Project Configuration & Types
        Route::apiResource('cibles', CibleController::class);
        Route::apiResource('types-intervention', TypeInterventionController::class)
            ->parameters(['types-intervention' => 'type_intervention']);

        Route::apiResource('types-programme', TypeProgrammeController::class)
            ->parameters(['types-programme' => 'type_programme']);

        Route::prefix('programmes')->name('programmes.')->controller(TypeProgrammeController::class)->group(function () {
            Route::get("{idProgramme}/composants-programme", "composants_de_programme");
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

        Route::prefix('canevas-check-liste-suivi-controle-qualite-rapport-etude-faisabilite-preliminaire')->group(function () {
            Route::get('', [DocumentController::class, 'canevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire']);
            Route::post('', [DocumentController::class, 'createOrUpdateCanevasChecklisteSuiviControleQualiteRapportEtudeFaisabilitePreliminaire']);
        });

        Route::get('canevas-check-listes-suivi-rapport-etude-faisabilite', [DocumentController::class, 'canevasChecklistesSuiviRapportEtudeFaisabilite']);

        Route::apiResource('categories-document', CategorieDocumentController::class)
            ->parameters(['categories-document' => 'categorie_document']);

        // Workflow & Process Management
        Route::apiResource('workflows', WorkflowController::class);

        // Commentaire
        Route::get('commentaires/{resourceType}/{resourceId}', [CommentaireController::class, 'getByResource'])
            ->name('commentaires.by-resource');
        Route::apiResource('commentaires', CommentaireController::class);

        // Fichier - Routes personnalisées AVANT la resource
        Route::get('visualiserFichier/{hash}', [FichierController::class, 'visualiserFichierParHash'])->name('fichiers.view');
        Route::get('telechargerFichier/{hash}', [FichierController::class, 'telechargerFichierParHash'])->name('fichiers.download');

        // Routes de partage de fichiers
        Route::prefix('fichiers')->group(function () {
            Route::post('{fichier}/partager', [FichierController::class, 'partager'])->name('fichiers.partager');
            Route::get('partages-avec-moi', [FichierController::class, 'fichiersPartagesAvecMoi'])->name('fichiers.partages-avec-moi');
            Route::get('recents', [FichierController::class, 'fichiers Recents'])->name('fichiers.recents');
        });

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
        /*Route::prefix('evaluations')->group(function () {
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
        });*/

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
        Route::prefix('idees-projet/{ideeProjetId}/evaluation-de-la-pertinence')->group(function () {
            Route::post('/', [EvaluationController::class, 'soumettreEvaluationPertinence'])
                ->name('idees-projet.evaluation-pertinence.create');
            Route::get('/', [EvaluationController::class, 'getDashboardEvaluationPertinence'])
                ->name('idees-projet.evaluation-pertinence.dashboard');
            Route::post('/refaire', [EvaluationController::class, 'refaireAutoEvaluationPertinence'])
                ->name('idees-projet.evaluation-pertinence.refaire');
        });

        // Route pour obtenir le score de pertinence
        Route::get('evaluations/{evaluationId}/pertinence/score', [EvaluationController::class, 'getScorePertinence'])
            ->name('evaluations.pertinence.score');

        // Route pour finaliser l'évaluation de pertinence
        Route::post('evaluations/{evaluationId}/valider-evaluation-de-la-pertinence', [EvaluationController::class, 'finaliserAutoEvaluationPertinence'])
            ->name('evaluations.pertinence.finalize');

        // Route pour finaliser l'évaluation de pertinence
        Route::post('evaluations/{evaluationId}/refaire-evaluation-de-la-pertinence', [EvaluationController::class, 'refaireAutoEvaluationPertinence'])
            ->name('evaluations.pertinence.finalize');


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
        Route::prefix('grille-evaluation-pertinence')->group(function () {
            Route::get('/', [\App\Http\Controllers\CategorieCritereController::class, 'getGrilleEvaluationPertinence'])
                ->name('grille-evaluation-pertinence.get');
            Route::post('/', [\App\Http\Controllers\CategorieCritereController::class, 'updateGrilleEvaluationPertinence'])
                ->name('grille-evaluation-pertinence.update');
            Route::get('/{idee_projet_id}', [\App\Http\Controllers\CategorieCritereController::class, 'getGrilleEvaluationPertinenceAvecEvaluations'])
                ->name('grille-evaluation-pertinence.get-avec-evaluations')
                ->where('idee_projet_id', '[0-9]+');
        });

        // Checklist des mesures d'adaptation pour projets à haut risque
        Route::get('checklist-mesures-adaptation/{secteurId}/secteur', [\App\Http\Controllers\CategorieCritereController::class, 'getChecklistMesuresAdaptationSecteur'])
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
    // ENUMROUTES - For Frontend Dropdown Options
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
            return response()->json([
                'statut'        => "success",
                'message'       => "Liste des sous d'idee de projet",
                'data'          => \App\Enums\SousPhaseIdee::options(),
                'statutCode'    => 200
            ]);
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

    //

    // Étape 1 : Le front demande l'URL SSO
    Route::get('/ad-auth/redirect', function () {

        $state = Str::uuid()->toString();
        $callbackUrl = config('services.gov.redirect');

        // Stocker les données du state pour 5 minutes
        $stateData = [
            'frontend_origin' => request('frontend_origin', env('FRONTEND_URL')),
            'activation_mode' => request('activation_mode', false),
            'email' => request('email'),
        ];

        Cache::put("oauth_state:{$state}", $stateData, 300);

        // Log pour vérifier que les données sont bien stockées
        \Illuminate\Support\Facades\Log::info('OAuth State stocké', [
            'state' => $state,
            'data' => $stateData,
            'cache_key' => "oauth_state:{$state}",
            'verification' => Cache::get("oauth_state:{$state}")
        ]);

        $params = http_build_query([
            'client_id' => config('services.gov.client_id'),
            'redirect_uri' => $callbackUrl,
            'response_type' => 'code',
            'scope' => 'openid',
            'state' => $state,
            'authError' => 'true',
        ]);

        return response()->json([
            'url' => config('services.gov.url') . '/official/login?' . $params,
        ]);
    });


    // Étape 2 : Le SSO renvoie ici après login
    /*Route::get('/callback', function (Request $request) {

        $code = $request->query('code');
        $state = $request->query('state');

        // Retrouver l'origine front correspondante
        $frontendUrl = Cache::pull("oauth_state:{$state}", env('FRONTEND_URL'));
        if (!$frontendUrl || !$code) {
            return response()->json(['error' => 'Session expirée ou invalide'], 400);
        }
        /* if (!$code) {
            return response()->json(['error' => 'Code manquant'], 400);
        } /

        // Échange du code contre un token
        $response = Http::asForm()
            ->withBasicAuth(config('services.gov.client_id'), config('services.gov.client_secret'))
            ->post(config('services.gov.url') . '/api/official/token', [
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('services.gov.redirect'),
                'code' => $code,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Impossible d\'obtenir le token'], 401);
        }

        $tokenData = $response->json();
        \Illuminate\Support\Facades\Log::info(json_encode($tokenData));
        $idToken = $tokenData['id_token'] ?? null;

        if (!$idToken) {
            return response()->json(['error' => 'Token manquant'], 401);
        }

        // Décodage du JWT pour obtenir les infos utilisateur
        $payload = json_decode(base64_decode(explode('.', $idToken)[1]), true);

        \Illuminate\Support\Facades\Log::info($payload);

        // 🔐 On chiffre le token avant de le renvoyer dans l'URL
        $encryptedToken = Crypt::encryptString($idToken);

        // Rediriger le navigateur du SSO vers ton front Vue
        $frontendUrl = env('FRONTEND_URL', 'http://192.168.1.5:3001');
        \Illuminate\Support\Facades\Log::info($frontendUrl);
        return redirect("{$frontendUrl}/auth/success?token={$encryptedToken}");
    });*/

    Route::get('/callback', function (Request $request) {

        $code = $request->query('code');
        $state = $request->query('state');

        // Log pour déboguer
        \Illuminate\Support\Facades\Log::info('Callback reçu', [
            'code' => $code ? 'présent' : 'absent',
            'state' => $state,
            'cache_key' => "oauth_state:{$state}"
        ]);

        // Récupérer les données du state depuis le cache avec valeur par défaut
        $stateData = Cache::pull("oauth_state:{$state}", [
            'frontend_origin' => env('FRONTEND_URL', 'http://192.168.8.105:3000'),
            'activation_mode' => false,
            'email' => null
        ]);

        // Log pour voir ce qui est récupéré du cache
        \Illuminate\Support\Facades\Log::info('Données récupérées du cache', [
            'stateData' => $stateData,
            'type' => gettype($stateData),
            'is_array' => is_array($stateData),
            'has_code' => !empty($code)
        ]);

        if (!$code) {
            \Illuminate\Support\Facades\Log::error('Code manquant', [
                'code_is_null' => is_null($code),
                'code_is_empty' => empty($code)
            ]);
            return response()->json(['error' => 'Code manquant'], 400);
        }

        $frontendUrl = $stateData['frontend_origin'] ?? env('FRONTEND_URL', 'http://192.168.8.105:3000');

        // Échange du code contre un token
        $response = Http::asForm()
            ->withBasicAuth(config('services.gov.client_id'), config('services.gov.client_secret'))
            ->post(config('services.gov.url') . '/api/official/token', [
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('services.gov.redirect'),
                'code' => $code,
            ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Impossible d\'obtenir le token'], 401);
        }

        $tokenData = $response->json();
        \Illuminate\Support\Facades\Log::info(json_encode($tokenData));
        $idToken = $tokenData['id_token'] ?? null;

        if (!$idToken) {
            return response()->json(['error' => 'Token manquant'], 401);
        }

        // Décodage du JWT pour obtenir les infos utilisateur
        \Illuminate\Support\Facades\Log::info('ID Token brut', ['id_token' => $idToken]);

        $jwtParts = explode('.', $idToken);
        \Illuminate\Support\Facades\Log::info('JWT Parts', [
            'parts_count' => count($jwtParts),
            'has_header' => isset($jwtParts[0]),
            'has_payload' => isset($jwtParts[1]),
            'has_signature' => isset($jwtParts[2])
        ]);

        if (count($jwtParts) < 2) {
            \Illuminate\Support\Facades\Log::error('JWT invalide - pas assez de parties');
            return response()->json(['error' => 'Token JWT invalide'], 400);
        }

        $payloadEncoded = $jwtParts[1];
        \Illuminate\Support\Facades\Log::info('Payload encodé', ['payload_encoded' => $payloadEncoded]);

        $payloadDecoded = base64_decode($payloadEncoded);
        \Illuminate\Support\Facades\Log::info('Payload décodé (base64)', ['payload_decoded' => $payloadDecoded]);

        $payload = json_decode($payloadDecoded, true);
        \Illuminate\Support\Facades\Log::info('Payload JSON parsé', ['payload' => $payload, 'type' => gettype($payload)]);

        // Récupérer l'email depuis le payload
        $email = $payload['email'] ?? $payload['upn'] ?? $payload['preferred_username'] ?? $payload['sub'] ?? $stateData['email'] ?? null;

        \Illuminate\Support\Facades\Log::info('Email extrait', [
            'email' => $email,
            'available_fields' => $payload ? array_keys($payload) : []
        ]);

        if (!$email) {
            return response()->json(['error' => 'Email manquant dans le token'], 400);
        }

        // Vérifier si l'utilisateur existe dans notre système
        $utilisateur = \App\Models\User::where('email', $email)->first();

        if (!$utilisateur) {
            return redirect("{$frontendUrl}/auth/error?message=" . urlencode('Utilisateur non trouvé dans le système BIP '.$email));
        }

        // Vérifier si c'est un mode d'activation
        $isActivationMode = $stateData['activation_mode'] ?? false;
        $compteDejaActive = $utilisateur->email_verified_at !== null && in_array($utilisateur->statut, ["actif", "invité"]);

        // Activer le compte si pas encore activé et en mode activation
        if (/* $isActivationMode &&  */!$compteDejaActive) {
            // Activation du compte
            if ($utilisateur->email_verified_at === null) {
                $utilisateur->email_verified_at = now();
            }

            if ($utilisateur->statut === "invité") {
                $utilisateur->statut = "actif";
            }

            //$utilisateur->first_connexion = now();

            // Nettoyer les données de vérification
            $utilisateur->account_verification_request_sent_at = null;
            $utilisateur->link_is_valide = false;
            $utilisateur->token = null;
        }

        $utilisateur->last_connection = now();
        $utilisateur->save();

        // Générer le token d'authentification BIP (Passport)
        $bipToken = $utilisateur->createToken('Bip-Token')->toArray();

        // Log de l'activation
        $acteur = $utilisateur->personne ? $utilisateur->personne->nom . " " . $utilisateur->personne->prenom : "Inconnu";
        $message = $compteDejaActive
            ? "{$acteur} s'est connecté via AD."
            : "{$acteur} a activé son compte et s'est connecté via AD.";

        \Illuminate\Support\Facades\Log::info($message, [
            'user_id' => $utilisateur->id,
            'email' => $utilisateur->email,
            'compte_active' => !$compteDejaActive
        ]);

        // 🔐 Chiffrer les données à renvoyer
        $dataToEncrypt = json_encode([
            //'id_token' => $idToken,
            ...$bipToken,
            'compte_nouvellement_active' => !$compteDejaActive
        ]);
        $encryptedToken = Crypt::encryptString($dataToEncrypt);

        // Rediriger vers le front avec le token chiffré
        return redirect("{$frontendUrl}/auth/success?token={$encryptedToken}");
    });

    Route::post('/auth/decrypt', function (Request $request) {
        try {
            $token = Crypt::decryptString($request->input('token'));

            // Retourner le token
            return response()->json(['statut' => 'success', 'message' => 'Authentification réussi', 'data' => (json_decode($token)), 'statutCode' => Response::HTTP_OK], Response::HTTP_OK)/*->withCookie('XSRF-TOKEN', $data['access_token'], 60*3)*/;

            return response()->json(['api_token' => $token]);
        } catch (Exception $e) {
            return response()->json(['error' => 'Token invalide'], 400);
        }
    });

    //Route::get("callback", [PassportClientController::class, 'handleProviderCallback'])->name('ad.oauth.callback');

});
// =============================================================================
// AUTHENTICATIONROUTES (Keycloak)
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

// Route de test pour auth:api
Route::get('/test-auth', function() {
    return response()->json([
        'success' => true,
        'user' => auth()->user()->email
    ]);
})->middleware('auth:api');

// Route de test pour le broadcasting des notifications
Route::post('/test-notification', function () {
    $user = auth()->user();

    $user->notify(new \App\Notifications\NotificationCreated(
        title: 'Test Notification',
        message: 'Ceci est un test de notification en temps réel',
        type: 'test',
        data: ['test' => true]
    ));

    return response()->json([
        'message' => 'Notification envoyée',
        'user' => $user->email
    ]);
})->middleware('auth:api');

// Route de test SYNCHRONE (sans queue) pour le broadcasting
Route::post('/test-notification-sync', function () {
    $user = auth()->user();

    $user->notify(new \App\Notifications\NotificationCreatedSync(
        title: 'Test Notification SYNC',
        message: 'Ceci est un test de notification SYNCHRONE en temps réel',
        type: 'test',
        data: ['test' => true, 'sync' => true]
    ));

    return response()->json([
        'message' => 'Notification SYNCHRONE envoyée',
        'user' => $user->email,
        'user_id' => $user->id,
        'user_hashed_id' => $user->hashed_id ?? 'N/A'
    ]);
})->middleware('auth:api');
