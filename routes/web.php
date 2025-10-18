<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Keycloak callback route (outside API prefix)
Route::match(['GET', 'POST'], '/auth/callback', [AuthController::class, 'callback']);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-echo', function () {
    return view('echo-test');
});
