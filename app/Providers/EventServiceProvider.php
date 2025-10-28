<?php

namespace App\Providers;

use App\Models\Organisation;
use App\Observers\OrganisationObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        \App\Events\IdeeProjetCree::class => [
            //\App\Listeners\NotifierIdeeProjetSoumise::class,
            \App\Listeners\CreerEvaluationClimatique::class,
        ],
        \App\Events\IdeeProjetTransformee::class => [
            \App\Listeners\DupliquerIdeeProjetVersProjet::class,
        ],
        \App\Events\CommentaireCreated::class => [
            \App\Listeners\SendCommentaireNotifications::class,
        ],

        // Notes Conceptuelles
        \App\Events\NoteConceptuelleSoumise::class => [
            \App\Listeners\NotifierNoteConceptuelleSoumise::class,
        ],

        // Appréciations Notes Conceptuelles
        \App\Events\AppreciationNoteConceptuelleCreee::class => [
            \App\Listeners\NotifierAppreciationNoteConceptuelleCreee::class,
        ],

        // Validation Étude de Profil
        \App\Events\EtudeProfilValidee::class => [
            \App\Listeners\NotifierEtudeProfilValidee::class,
        ],

        // Rapports de Faisabilité Préliminaire
        \App\Events\RapportFaisabilitePrelimSoumis::class => [
            \App\Listeners\NotifierRapportFaisabilitePrelimSoumis::class,
        ],

        // TDR de Préfaisabilité
        \App\Events\TdrPrefaisabiliteSoumis::class => [
            \App\Listeners\NotifierTdrPrefaisabiliteSoumis::class,
        ],

        // Rapports de Préfaisabilité
        \App\Events\RapportPrefaisabiliteSoumis::class => [
            \App\Listeners\NotifierRapportPrefaisabiliteSoumis::class,
        ],

        // Évaluation TDR de Préfaisabilité
        \App\Events\TdrPrefaisabiliteEvalue::class => [
            \App\Listeners\NotifierTdrPrefaisabiliteEvalue::class,
        ],

        // Validation Étude de Préfaisabilité
        \App\Events\EtudePrefaisabiliteValidee::class => [
            \App\Listeners\NotifierEtudePrefaisabiliteValidee::class,
        ],

        // TDR de Faisabilité
        \App\Events\TdrFaisabiliteSoumis::class => [
            \App\Listeners\NotifierTdrFaisabiliteSoumis::class,
        ],

        // Rapports de Faisabilité
        \App\Events\RapportFaisabiliteSoumis::class => [
            \App\Listeners\NotifierRapportFaisabiliteSoumis::class,
        ],

        // Évaluation TDR de Faisabilité
        \App\Events\TdrFaisabiliteEvalue::class => [
            \App\Listeners\NotifierTdrFaisabiliteEvalue::class,
        ],

        // Validation Étude de Faisabilité
        \App\Events\EtudeFaisabiliteValidee::class => [
            \App\Listeners\NotifierEtudeFaisabiliteValidee::class,
        ],

        // Rapport Évaluation Ex-Ante
        \App\Events\RapportEvaluationExAnteSoumis::class => [
            \App\Listeners\NotifierRapportEvaluationExAnteSoumis::class,
        ],

        // Validation Rapport Évaluation Ex-Ante
        \App\Events\RapportEvaluationExAnteValide::class => [
            \App\Listeners\NotifierRapportEvaluationExAnteValide::class,
            \App\Listeners\PosterProjetSystemeExterne::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot(); // 👈 à ajouter si absent
        Organisation::observe(OrganisationObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
