<?php

namespace App\Listeners;

use App\Events\TdrPrefaisabiliteEvalue;
use App\Notifications\NotificationTdrPrefaisabiliteEvalue;
use App\Models\User;
use App\Models\Dpaf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifierTdrPrefaisabiliteEvalue implements ShouldQueue
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
    public function handle(TdrPrefaisabiliteEvalue $event): void
    {
        $tdr = $event->tdr;
        $projet = $event->projet;
        $evaluation = $event->evaluation;
        $evaluateur = $event->evaluateur;
        $resultatsEvaluation = $event->resultatsEvaluation;

        Log::info('Envoi de notifications pour évaluation de TDR de préfaisabilité', [
            'tdr_id' => $tdr->id,
            'projet_id' => $projet->id,
            'resultat' => $resultatsEvaluation['resultat_global'] ?? null,
        ]);

        // 1. Notifier le rédacteur du TDR (résultat de l'évaluation)
        if ($tdr->rediger_par_id) {
            $redacteur = User::find($tdr->rediger_par_id);
            if ($redacteur) {
                $redacteur->notify(
                    new NotificationTdrPrefaisabiliteEvalue(
                        $tdr,
                        $projet,
                        $evaluation,
                        $evaluateur,
                        $resultatsEvaluation,
                        'redacteur_resultat'
                    )
                );
            }
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
                    new NotificationTdrPrefaisabiliteEvalue(
                        $tdr,
                        $projet,
                        $evaluation,
                        $evaluateur,
                        $resultatsEvaluation,
                        'dpaf_supervision'
                    )
                );
            }
        }

        // 3. Notifier toute l'équipe de l'organisation (information)
        if ($projet->organisation_id) {
            $membresOrganisation = User::where('profilable_type', 'App\Models\Organisation')
                ->where('profilable_id', $projet->organisation_id)
                ->get();

            if ($membresOrganisation->isNotEmpty()) {
                Notification::send(
                    $membresOrganisation,
                    new NotificationTdrPrefaisabiliteEvalue(
                        $tdr,
                        $projet,
                        $evaluation,
                        $evaluateur,
                        $resultatsEvaluation,
                        'equipe_organisation'
                    )
                );
            }
        }

        // 4. Notifier l'évaluateur (confirmation)
        $evaluateur->notify(
            new NotificationTdrPrefaisabiliteEvalue(
                $tdr,
                $projet,
                $evaluation,
                $evaluateur,
                $resultatsEvaluation,
                'evaluateur_confirmation'
            )
        );

        Log::info('Notifications envoyées avec succès pour évaluation de TDR de préfaisabilité', [
            'tdr_id' => $tdr->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(TdrPrefaisabiliteEvalue $event, \Throwable $exception): void
    {
        Log::error('Échec de notification TdrPrefaisabiliteEvalue', [
            'tdr_id' => $event->tdr->id,
            'projet_id' => $event->projet->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
