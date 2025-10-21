<?php

namespace App\Listeners;

use App\Events\RapportFaisabiliteSoumis;
use App\Notifications\NotificationRapportFaisabiliteSoumis;
use App\Models\User;
use App\Models\Dpaf;
use App\Models\Dgpd;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifierRapportFaisabiliteSoumis implements ShouldQueue
{
    use InteractsWithQueue;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RapportFaisabiliteSoumis $event): void
    {
        $rapport = $event->rapport;
        $projet = $event->projet;
        $soumetteur = $event->soumetteur;
        $estResoumission = $event->estResoumission;

        Log::info('Envoi de notifications pour soumission de rapport de faisabilité', [
            'rapport_id' => $rapport->id,
            'projet_id' => $projet->id,
            'est_resoumission' => $estResoumission,
        ]);

        // 1. Notifier les membres DGPD (validation requise)
        $membresDgpd = User::where('profilable_type', Dgpd::class)->get();

        if ($membresDgpd->isNotEmpty()) {
            Notification::send(
                $membresDgpd,
                new NotificationRapportFaisabiliteSoumis(
                    $rapport,
                    $projet,
                    $soumetteur,
                    $estResoumission,
                    'dgpd_validation'
                )
            );
        }

        // 2. Notifier le DPAF du ministère (supervision)
        if ($projet->ministere_id) {
            $dpafMinistere = User::where('profilable_type', Dpaf::class)
                ->whereHas('profilable', function($query) use ($projet) {
                    $query->where('ministere_id', $projet->ministere_id);
                })
                ->first();

            if ($dpafMinistere) {
                $dpafMinistere->notify(
                    new NotificationRapportFaisabiliteSoumis(
                        $rapport,
                        $projet,
                        $soumetteur,
                        $estResoumission,
                        'dpaf_supervision'
                    )
                );
            }
        }

        // 3. Notifier toute l'équipe de l'organisation (information)
        if ($projet->organisation_id) {
            $membresOrganisation = User::where('profilable_type', 'App\Models\Organisation')
                ->where('profilable_id', $projet->organisation_id)
                ->where('id', '!=', $soumetteur->id) // Exclure le soumetteur
                ->get();

            if ($membresOrganisation->isNotEmpty()) {
                Notification::send(
                    $membresOrganisation,
                    new NotificationRapportFaisabiliteSoumis(
                        $rapport,
                        $projet,
                        $soumetteur,
                        $estResoumission,
                        'equipe_organisation'
                    )
                );
            }
        }

        // 4. Notifier le soumetteur (confirmation)
        $soumetteur->notify(
            new NotificationRapportFaisabiliteSoumis(
                $rapport,
                $projet,
                $soumetteur,
                $estResoumission,
                'soumetteur_confirmation'
            )
        );

        Log::info('Notifications envoyées avec succès pour soumission de rapport de faisabilité', [
            'rapport_id' => $rapport->id,
            'est_resoumission' => $estResoumission,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(RapportFaisabiliteSoumis $event, \Throwable $exception): void
    {
        Log::error('Échec de notification RapportFaisabiliteSoumis', [
            'rapport_id' => $event->rapport->id,
            'projet_id' => $event->projet->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
