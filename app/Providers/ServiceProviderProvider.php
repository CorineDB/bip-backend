<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

class ServiceProviderProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // AbstractServiceInterface is abstract, no binding needed

        // All service bindings
        $services = [
            'RoleServiceInterface' => 'RoleService',
            'PermissionServiceInterface' => 'PermissionService',
            'OrganisationServiceInterface' => 'OrganisationService',
            'UserServiceInterface' => 'UserService',
            'PersonneServiceInterface' => 'PersonneService',
            'DepartementServiceInterface' => 'DepartementService',
            'CommuneServiceInterface' => 'CommuneService',
            'ArrondissementServiceInterface' => 'ArrondissementService',
            'VillageServiceInterface' => 'VillageService',
            'SecteurServiceInterface' => 'SecteurService',
            'CategorieProjetServiceInterface' => 'CategorieProjetService',
            'IdeeProjetServiceInterface' => 'IdeeProjetService',
            'ProjetServiceInterface' => 'ProjetService',
            'DecisionServiceInterface' => 'DecisionService',
            'CibleServiceInterface' => 'CibleService',
            'TypeInterventionServiceInterface' => 'TypeInterventionService',
            'TypeProgrammeServiceInterface' => 'TypeProgrammeService',
            'ComposantProgrammeServiceInterface' => 'ComposantProgrammeService',
            'WorkflowServiceInterface' => 'WorkflowService',
            'FinancementServiceInterface' => 'FinancementService',
            'OddServiceInterface' => 'OddService',
            'CategorieDocumentServiceInterface' => 'CategorieDocumentService',
            'TrackInfoServiceInterface' => 'TrackInfoService',
            'DocumentServiceInterface' => 'DocumentService',
            'NoteConceptuelleServiceInterface' => 'NoteConceptuelleService',
            'ChampServiceInterface' => 'ChampService',
            'EvaluationServiceInterface' => 'EvaluationService',
            "PassportOAuthServiceInterface" => "PassportOAuthService",
            'DpafServiceInterface' => 'DpafService',
            'DgpdServiceInterface' => 'DgpdService',
            'GroupeUtilisateurServiceInterface' => 'GroupeUtilisateurService',
            'CategorieCritereServiceInterface' => 'CategorieCritereService',
            'NotificationServiceInterface' => 'NotificationService',
            'TdrPrefaisabiliteServiceInterface' => 'TdrPrefaisabiliteService',
            'TdrFaisabiliteServiceInterface' => 'TdrFaisabiliteService'
        ];

        foreach ($services as $interface => $implementation) {
            $this->app->bind(
                "App\\Services\\Contracts\\{$interface}",
                "App\\Services\\{$implementation}"
            );
        }

        $contractPath = app_path('Services/Contracts');

        if (!File::exists($contractPath) || !File::isDirectory($contractPath)) {
            return;
        }

        // Auto-discovery is now replaced by manual bindings above
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
