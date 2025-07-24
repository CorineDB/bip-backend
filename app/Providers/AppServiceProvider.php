<?php

namespace App\Providers;

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
        Passport::loadKeysFrom(storage_path('secrets/oauth/'));

        //Passport::loadKeysFrom(__DIR__.'/../secrets/oauth');
        Passport::tokensExpireIn(CarbonInterval::hours(8));
        Passport::refreshTokensExpireIn(CarbonInterval::hours(3));
        Passport::personalAccessTokensExpireIn(CarbonInterval::days(15));
        Passport::enablePasswordGrant();
    }
}
