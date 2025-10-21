<?php

namespace App\Listeners;

use App\Events\NoteConceptuelleSoumise;
use App\Notifications\NoteConceptuelleSoumiseNotification;
use App\Models\User;
use App\Models\Dpaf;
use App\Models\Dgpd;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifierNoteConceptuelleSoumise implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the job may be attempted.
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public $backoff = [10, 30, 60];

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(NoteConceptuelleSoumise $event): void
    {
        $noteConceptuelle = $event->noteConceptuelle;
        $projet = $event->projet;
        $acteur = $event->acteur;

        Log::info('Envoi de notifications pour soumission de note conceptuelle', [
            'note_conceptuelle_id' => $noteConceptuelle->id,
            'projet_id' => $projet->id,
            'acteur_id' => $acteur->id,
        ]);

        // 1. Notifier le rédacteur (confirmation)
        if ($noteConceptuelle->redacteur) {
            $noteConceptuelle->redacteur->notify(
                new NoteConceptuelleSoumiseNotification(
                    $noteConceptuelle,
                    $projet,
                    'confirmation'
                )
            );
        }

        // 2. Notifier tous les utilisateurs DGPD (évaluation requise)
        $utilisateursDgpd = User::where('profilable_type', Dgpd::class)
            ->whereHas('permissions', function($query) {
                $query->where('slug', 'evaluer-une-note-conceptuelle');
            })
            ->get();

        if ($utilisateursDgpd->isNotEmpty()) {
            Notification::send(
                $utilisateursDgpd,
                new NoteConceptuelleSoumiseNotification(
                    $noteConceptuelle,
                    $projet,
                    'evaluation_requise'
                )
            );
        }

        // 3. Notifier le DPAF du ministère (information)
        if ($projet->ministere_id) {
            $dpafMinistere = User::where('profilable_type', Dpaf::class)
                ->whereHas('profilable', function($query) use ($projet) {
                    $query->where('ministere_id', $projet->ministere_id);
                })
                ->first();

            if ($dpafMinistere) {
                $dpafMinistere->notify(
                    new NoteConceptuelleSoumiseNotification(
                        $noteConceptuelle,
                        $projet,
                        'information'
                    )
                );
            }
        }

        // 4. Notifier le chef de projet du ministère
        if ($projet->organisation_id) {
            $chefProjetMinistere = User::where('profilable_type', 'App\Models\Organisation')
                ->where('profilable_id', $projet->organisation_id)
                ->whereHas('roles', function($query) {
                    $query->where('slug', 'responsable-projet');
                })
                ->first();

            if ($chefProjetMinistere && $chefProjetMinistere->id !== $acteur->id) {
                $chefProjetMinistere->notify(
                    new NoteConceptuelleSoumiseNotification(
                        $noteConceptuelle,
                        $projet,
                        'information'
                    )
                );
            }
        }

        Log::info('Notifications envoyées avec succès pour soumission de note conceptuelle', [
            'note_conceptuelle_id' => $noteConceptuelle->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(NoteConceptuelleSoumise $event, \Throwable $exception): void
    {
        Log::error('Échec de notification NoteConceptuelleSoumise', [
            'note_conceptuelle_id' => $event->noteConceptuelle->id,
            'projet_id' => $event->projet->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
