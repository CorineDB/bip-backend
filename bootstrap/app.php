<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'keycloak' => \App\Http\Middleware\KeycloakAuth::class,
            'cors' => \App\Http\Middleware\CorsMiddleware::class,

            'json.response' => \App\Http\Middleware\ForceJsonResponse::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
            'auth.client' => \Laravel\Passport\Http\Middleware\CheckToken::class,
            'scope' => \Laravel\Passport\Http\Middleware\CheckTokenForAnyScope::class,
        ]);
        $middleware->use([
            \App\Http\Middleware\CorsMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Configuration des exceptions API en utilisant ExceptionTrait
        $exceptions->render(function (Throwable $exception, $request) {
            // Pour les requêtes API, retourner du JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                $handler = new class {
                    use \App\Services\Traits\ExceptionTrait;
                };
                return $handler->apiExceptions($request, $exception);
            }
            return null; // Laisser Laravel gérer les autres
        });
    })->create();
