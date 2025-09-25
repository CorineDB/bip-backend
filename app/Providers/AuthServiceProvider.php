<?php

namespace App\Providers;

use App\Models\IdeeProjet;
use App\Models\Projet;
use App\Models\CategorieDocument;
use App\Models\Document;
use App\Models\CategorieCritere;
use App\Policies\IdeeProjetPolicy;
use App\Policies\ProjetPolicy;
use App\Policies\CategorieDocumentPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\CategorieCriterePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        IdeeProjet::class => IdeeProjetPolicy::class,
        Projet::class => ProjetPolicy::class,
        CategorieDocument::class => CategorieDocumentPolicy::class,
        Document::class => DocumentPolicy::class,
        CategorieCritere::class => CategorieCriterePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Configuration Passport si nécessaire
        // Configuration des tokens d'accès (optionnel)
        Passport::tokensExpireIn(now()->addDays(15));
        Passport::refreshTokensExpireIn(now()->addDays(30));
        Passport::personalAccessTokensExpireIn(now()->addMonths(6));

        // Gates supplémentaires si nécessaire
        Gate::define('super-admin', function ($user) {
            return $user->role?->slug === 'super-admin';
        });
    }
}
