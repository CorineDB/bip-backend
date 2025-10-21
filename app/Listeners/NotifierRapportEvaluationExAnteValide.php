<?php

namespace App\Listeners;

use App\Events\RapportEvaluationExAnteValide;
use App\Notifications\NotificationRapportEvaluationExAnteValide;
use App\Models\User;
use App\Models\Dpaf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifierRapportEvaluationExAnteValide implements ShouldQueue
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
    public function handle(RapportEvaluationExAnteValide $event): void
    {
        $rapport = $event->rapport;
        $projet = $event->projet;
        $evaluation = $event->evaluation;
        $validateur = $event->validateur;
        $decision = $event->decision;

        Log::info('Envoi de notifications pour validation d\'étude de préfaisabilité', [
            'rapport_id' => $rapport->id,
            'projet_id' => $projet->id,
            'decision' => $decision,
        ]);

        // 1. Notifier le soumetteur du rapport (résultat de la validation)
        if ($rapport->soumis_par_id) {
            $soumetteur = User::find($rapport->soumis_par_id);
            if ($soumetteur) {
                $soumetteur->notify(
                    new NotificationRapportEvaluationExAnteValide(
                        $rapport,
                        $projet,
                        $evaluation,
                        $validateur,
                        $decision,
                        'redacteur_resultat'
                    )
                );
            }
        }

        // 2. Notifier toute l'équipe de l'organisation (changement important)
        if ($projet->organisation_id) {
            $membresOrganisation = User::where('profilable_type', 'App\Models\Organisation')
                ->where('profilable_id', $projet->organisation_id)
                ->get();

            if ($membresOrganisation->isNotEmpty()) {
                Notification::send(
                    $membresOrganisation,
                    new NotificationRapportEvaluationExAnteValide(
                        $rapport,
                        $projet,
                        $evaluation,
                        $validateur,
                        $decision,
                        'equipe_organisation'
                    )
                );
            }
        }

        // 3. Notifier le DPAF du ministère (supervision)
        if ($projet->ministere_id) {
            $dpafMinistere = User::where('profilable_type', Dpaf::class)
                ->whereHas('profilable', function($query) use ($projet) {
                    $query->where('ministere_id', $projet->ministere_id);
                })
                ->first();

            if ($dpafMinistere) {
                $dpafMinistere->notify(
                    new NotificationRapportEvaluationExAnteValide(
                        $rapport,
                        $projet,
                        $evaluation,
                        $validateur,
                        $decision,
                        'dpaf_supervision'
                    )
                );
            }
        }

        // 4. Notifier le validateur (confirmation)
        $validateur->notify(
            new NotificationRapportEvaluationExAnteValide(
                $rapport,
                $projet,
                $evaluation,
                $validateur,
                $decision,
                'validateur_confirmation'
            )
        );

        // 5. Si décision = faire étude de faisabilité, notifier les chargés d'études
        if ($decision === 'faisabilite') {
            $chargesEtudes = User::where('profilable_type', 'App\Models\Organisation')
                ->where('profilable_id', $projet->organisation_id)
                ->whereHas('roles', function($query) {
                    $query->where('slug', 'charge-etudes');
                })
                ->get();

            if ($chargesEtudes->isNotEmpty()) {
                Notification::send(
                    $chargesEtudes,
                    new NotificationRapportEvaluationExAnteValide(
                        $rapport,
                        $projet,
                        $evaluation,
                        $validateur,
                        $decision,
                        'charge_faisabilite_action'
                    )
                );
            }
        }

        Log::info('Notifications envoyées avec succès pour validation d\'étude de préfaisabilité', [
            'rapport_id' => $rapport->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(RapportEvaluationExAnteValide $event, \Throwable $exception): void
    {
        Log::error('Échec de notification RapportEvaluationExAnteValide', [
            'rapport_id' => $event->rapport->id,
            'projet_id' => $event->projet->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
