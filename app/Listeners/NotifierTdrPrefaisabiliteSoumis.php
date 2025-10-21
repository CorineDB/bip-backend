<?php

namespace App\Listeners;

use App\Events\TdrPrefaisabiliteSoumis;
use App\Notifications\NotificationTdrPrefaisabiliteSoumis;
use App\Models\User;
use App\Models\Dpaf;
use App\Models\Dgpd;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifierTdrPrefaisabiliteSoumis implements ShouldQueue
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
    public function handle(TdrPrefaisabiliteSoumis $event): void
    {
        $tdr = $event->tdr;
        $projet = $event->projet;
        $soumetteur = $event->soumetteur;
        $estResoumission = $event->estResoumission;

        Log::info('Envoi de notifications pour soumission de TDR de préfaisabilité', [
            'tdr_id' => $tdr->id,
            'projet_id' => $projet->id,
            'est_resoumission' => $estResoumission,
        ]);

        // 1. Notifier les membres DGPD (évaluation requise)
        $membresDgpd = User::where('profilable_type', Dgpd::class)->get();

        if ($membresDgpd->isNotEmpty()) {
            Notification::send(
                $membresDgpd,
                new NotificationTdrPrefaisabiliteSoumis(
                    $tdr,
                    $projet,
                    $soumetteur,
                    $estResoumission,
                    'dgpd_evaluation'
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
                    new NotificationTdrPrefaisabiliteSoumis(
                        $tdr,
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
                    new NotificationTdrPrefaisabiliteSoumis(
                        $tdr,
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
            new NotificationTdrPrefaisabiliteSoumis(
                $tdr,
                $projet,
                $soumetteur,
                $estResoumission,
                'soumetteur_confirmation'
            )
        );

        Log::info('Notifications envoyées avec succès pour soumission de TDR de préfaisabilité', [
            'tdr_id' => $tdr->id,
            'est_resoumission' => $estResoumission,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(TdrPrefaisabiliteSoumis $event, \Throwable $exception): void
    {
        Log::error('Échec de notification TdrPrefaisabiliteSoumis', [
            'tdr_id' => $event->tdr->id,
            'projet_id' => $event->projet->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
