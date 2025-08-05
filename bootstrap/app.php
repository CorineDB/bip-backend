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
            //'cors' => \App\Http\Middleware\CorsMiddleware::class,

            'json.response' => \App\Http\Middleware\ForceJsonResponse::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'permission' => \App\Http\Middleware\PermissionMiddleware::class,
        ]);
        $middleware->use([
            \App\Http\Middleware\CorsMiddleware::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /*
            $exceptions->render(function (Throwable $exception, Request $request) {

                if ($request->expectsJson() || $request->wantsJson() || $request->isJson() || $request->is('api/*'))
                {
                    return $this->apiExceptions($request,$exception);
                }
                return response();
            });
        */
        //
    })->create();
