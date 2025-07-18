<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Import all controllers
use App\Http\Controllers\ArrondissementController;
use App\Http\Controllers\CategorieDocumentController;
use App\Http\Controllers\CategorieProjetController;
use App\Http\Controllers\ChampController;
use App\Http\Controllers\CibleController;
use App\Http\Controllers\CommuneController;
use App\Http\Controllers\ComposantProgrammeController;
use App\Http\Controllers\DecisionController;
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
use App\Http\Controllers\TrackInfoController;
use App\Http\Controllers\TypeInterventionController;
use App\Http\Controllers\TypeProgrammeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VillageController;
use App\Http\Controllers\WorkflowController;

// Get authenticated user
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// =============================================================================
// API RESOURCE ROUTES - Full CRUD Controllers (23 controllers)
// =============================================================================

// Geographic & Administrative Resources
Route::apiResource('arrondissements', ArrondissementController::class)->only(['index', 'show']);
Route::apiResource('communes', CommuneController::class)->only(['index', 'show']);
Route::apiResource('departements', DepartementController::class)->only(['index', 'show']);
Route::apiResource('villages', VillageController::class)->only(['index', 'show']);

// Organization & People Management
Route::apiResource('organisations', OrganisationController::class);
Route::apiResource('personnes', PersonneController::class);

// User Management & Security (read-only permissions)
Route::apiResource('users', UserController::class);
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

// Project Configuration & Types
Route::apiResource('cibles', CibleController::class);
Route::apiResource('types-intervention', TypeInterventionController::class)
    ->parameters(['types-intervention' => 'type_intervention']);

Route::apiResource('types-programme', TypeProgrammeController::class)
    ->parameters(['types-programme' => 'type_programme']);
Route::apiResource('composants-programme', ComposantProgrammeController::class)
    ->parameters(['composants-programme' => 'composant_programme']);

// Document Management
Route::apiResource('documents', DocumentController::class);
Route::apiResource('categories-document', CategorieDocumentController::class);

// Workflow & Process Management
Route::apiResource('workflows', WorkflowController::class);
Route::apiResource('decisions', DecisionController::class);
Route::apiResource('track-infos', TrackInfoController::class);

// Financial & Evaluation
Route::apiResource('financements', FinancementController::class);
Route::apiResource('evaluations', EvaluationController::class);
Route::apiResource('champs', ChampController::class);

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

// =============================================================================
// AUTHENTICATION ROUTES (Laravel Sanctum)
// =============================================================================

Route::prefix('auth')->group(function () {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'register']);
    Route::middleware('auth:sanctum')->post('/logout', [UserController::class, 'logout']);
    Route::middleware('auth:sanctum')->get('/profile', [UserController::class, 'profile']);
    Route::middleware('auth:sanctum')->put('/profile', [UserController::class, 'updateProfile']);
    Route::middleware('auth:sanctum')->put('/change-password', [UserController::class, 'changePassword']);
});