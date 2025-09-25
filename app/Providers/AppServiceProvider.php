<?php

namespace App\Providers;

use App\Models\Organisation;
use App\Observers\OrganisationObserver;
use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {

    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //Passport::loadKeysFrom(__DIR__.'/../secrets/oauth');
        Passport::loadKeysFrom(base_path('app/secrets/oauth'));
        Passport::tokensExpireIn(CarbonInterval::hours(8));
        Passport::refreshTokensExpireIn(CarbonInterval::hours(3));
        Passport::personalAccessTokensExpireIn(CarbonInterval::days(15));
        Passport::enablePasswordGrant();

        Passport::tokensCan([
            'sync-sigfp' => 'Allow SIGFP to sync data with BIP',
            'integration-bip' => 'Allow Integration with BIP',
            'read-projects' => 'Read project data',
            'manage-projects' => 'Create, update, delete projects',
            'manage-clients' => 'Manage OAuth clients',
            'admin' => 'Full administrative access',
        ]);

    }
}
