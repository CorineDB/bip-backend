<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Enregistrer les routes de broadcasting (/broadcasting/auth)
        Broadcast::routes(['middleware' => ['auth:api']]);

        // Charger les définitions de canaux depuis routes/channels.php
        require base_path('routes/channels.php');
    }
}
