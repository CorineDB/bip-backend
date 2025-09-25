<?php

use App\Http\Controllers\API\Json\IntegrationBip\IntegrationController;
use Illuminate\Support\Facades\Route;

Route::prefix('integration-bip')->middleware(['auth.client', 'scope:integration-bip', 'oauth.audit'])->group(function () {

    Route::middleware([/* 'scopes:sync-sigfp' */])->group(function () {
        Route::get('/projets-mature', [IntegrationController::class, 'index'])
            ->name('projets.mature');
    });
});
