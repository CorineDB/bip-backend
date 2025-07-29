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
use App\Http\Controllers\GroupeUtilisateurController;
use App\Http\Controllers\OAuthController;
use App\Models\GroupeUtilisateur;

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


Route::group(['middleware' => [/* 'cors', */ 'json.response'], 'as' => 'api.'], function () {

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

                Route::post('reinitialisation-de-mot-de-passe', 'modificationDeMotDePasse')->name('modificationDeMotDePasse');

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

        Route::controller(OrganisationController::class)->group(function () {
            Route::get('ministeres', 'ministeres');
            Route::get('ministeres/{id}/organismes_tutelle', 'organismes_tutelle');
        });

        Route::apiResource('personnes', PersonneController::class);

        // User Management & Security (read-only permissions)
        Route::apiResource('users', UserController::class);
        Route::apiResource('groupes-utilisateur', GroupeUtilisateurController::class);
        Route::apiResource('roles', RoleController::class);
        Route::apiResource('permissions', PermissionController::class)->only(['index', 'show']);

        // Role-Permission Management
        Route::prefix('roles/{role}')->group(function () {
            Route::get('/permissions', [RoleController::class, 'getPermissions']);
        });

        // Project Management Core
        Route::apiResource('idees-projet', IdeeProjetController::class);

        Route::apiResource('projets', ProjetController::class);
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
        Route::apiResource('composants-programme', ComposantProgrammeController::class)
            ->parameters(['composants-programme' => 'composant_programme']);

        Route::controller(ComposantProgrammeController::class)->group(function () {
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

        Route::apiResource('categories-document', CategorieDocumentController::class);

        // Workflow & Process Management
        Route::apiResource('workflows', WorkflowController::class);

        // Financial & Evaluation
        Route::apiResource('financements', FinancementController::class);
        Route::apiResource('evaluations', EvaluationController::class);
        Route::apiResource('champs', ChampController::class);
        
        // Evaluation Criteria Management
        Route::apiResource('categories-critere', \App\Http\Controllers\CategorieCritereController::class);
        
        // Grille Evaluation Preliminaire des Impacts Climatique (Routes spécifiques)
        Route::prefix('grille-evaluation-preliminaire')->group(function () {
            Route::get('/', [\App\Http\Controllers\CategorieCritereController::class, 'getGrilleEvaluationPreliminaire'])
                ->name('grille-evaluation-preliminaire.get');
            Route::put('/', [\App\Http\Controllers\CategorieCritereController::class, 'updateGrilleEvaluationPreliminaire'])
                ->name('grille-evaluation-preliminaire.update');
        });

        // SDG Integration
        Route::apiResource('odds', OddController::class);

        // =============================================================================
        // ENUM ROUTES - For Frontend Dropdown Options
        // =============================================================================

        Route::prefix('enums')->group(function () {
            // Project Status & Workflow Enums
            Route::get('/statut-idee', function () {
                return response()->json(\App\Enums\StatutIdee::options());
            });

            Route::get('/phases-idee', function () {
                return response()->json(\App\Enums\PhasesIdee::options());
            });

            Route::get('/sous-phase-idee', function () {
                return response()->json(\App\Enums\SousPhaseIdee::options());
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
                return response()->json([
                    'statut_idee' => \App\Enums\StatutIdee::options(),
                    'phases_idee' => \App\Enums\PhasesIdee::options(),
                    'sous_phase_idee' => \App\Enums\SousPhaseIdee::options(),
                    'types_projet' => \App\Enums\TypesProjet::options(),
                    'types_canevas' => \App\Enums\TypesCanevas::options(),
                    'types_template' => \App\Enums\TypesTemplate::options(),
                    'types_organisation' => \App\Enums\EnumTypeOrganisation::options(),
                ]);
            });
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
