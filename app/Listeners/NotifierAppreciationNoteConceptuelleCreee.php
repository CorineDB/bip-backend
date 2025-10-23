<?php

namespace App\Listeners;

use App\Events\AppreciationNoteConceptuelleCreee;
use App\Notifications\AppreciationNoteConceptuelleNotification;
use App\Models\User;
use App\Models\Dpaf;
use App\Models\Dgpd;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class NotifierAppreciationNoteConceptuelleCreee implements ShouldQueue
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
    public function handle(AppreciationNoteConceptuelleCreee $event): void
    {
        $evaluation = $event->evaluation;
        $noteConceptuelle = $event->noteConceptuelle;
        $projet = $event->projet;
        $evaluateur = $event->evaluateur;
        $typeAppreciation = $event->typeAppreciation;
        $statut = $event->statut;

        Log::info('Envoi de notifications pour appréciation note conceptuelle', [
            'evaluation_id' => $evaluation->id,
            'note_conceptuelle_id' => $noteConceptuelle->id,
            'type_appreciation' => $typeAppreciation,
        ]);

        // 1. Notifier le rédacteur de la note conceptuelle (information)
        if ($noteConceptuelle->redacteur) {
            $noteConceptuelle->redacteur->notify(
                new AppreciationNoteConceptuelleNotification(
                    $evaluation,
                    $noteConceptuelle,
                    $projet,
                    $evaluateur,
                    'redacteur_info'
                )
            );
        }

        // 2. Notifier le DPAF du ministère (supervision)
        if ($projet->ministere_id) {
            $dpafMinistere = User::where('profilable_type', 'App\Models\Dpaf')
                ->whereHas('profilable', function($query) use ($projet) {
                    $query->where('ministere_id', $projet->ministere_id);
                })
                ->first();

            if ($dpafMinistere) {
                $dpafMinistere->notify(
                    new AppreciationNoteConceptuelleNotification(
                        $evaluation,
                        $noteConceptuelle,
                        $projet,
                        $evaluateur,
                        'dpaf_supervision'
                    )
                );
            }
        }

        // 3. Notifier les autres membres du DGPD (information collégiale)
        $autresDgpd = User::where('profilable_type', 'App\Models\Dgpd')
            ->where('id', '!=', $evaluateur->id)
            ->whereHas('permissions', function($query) {
                $query->where('slug', 'evaluer-une-note-conceptuelle');
            })
            ->get();

        if ($autresDgpd->isNotEmpty()) {
            Notification::send(
                $autresDgpd,
                new AppreciationNoteConceptuelleNotification(
                    $evaluation,
                    $noteConceptuelle,
                    $projet,
                    $evaluateur,
                    'dgpd_collegial'
                )
            );
        }

        // 4. Si l'évaluation est terminée, notifier le responsable du projet
        if (in_array($statut, ['terminee', 'validee', 'soumise'])) {
            if ($projet->organisation_id) {
                $chefProjet = User::where('profilable_type', 'App\Models\Organisation')
                    ->where('profilable_id', $projet->organisation_id)
                    ->whereHas('roles', function($query) use($projet) {
                        $query->where('slug', 'responsable-projet');
                        $query->where('id', $projet->ideeProjet->responsableId);
                    })
                    ->first();

                if ($chefProjet) {
                    $chefProjet->notify(
                        new AppreciationNoteConceptuelleNotification(
                            $evaluation,
                            $noteConceptuelle,
                            $projet,
                            $evaluateur,
                            'chef_projet_evaluation_terminee'
                        )
                    );
                }
            }
        }

        // 5. Notifier l'évaluateur lui-même (confirmation)
        $evaluateur->notify(
            new AppreciationNoteConceptuelleNotification(
                $evaluation,
                $noteConceptuelle,
                $projet,
                $evaluateur,
                'evaluateur_confirmation'
            )
        );

        Log::info('Notifications envoyées avec succès pour appréciation note conceptuelle', [
            'evaluation_id' => $evaluation->id,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(AppreciationNoteConceptuelleCreee $event, \Throwable $exception): void
    {
        Log::error('Échec de notification AppreciationNoteConceptuelleCreee', [
            'evaluation_id' => $event->evaluation->id,
            'note_conceptuelle_id' => $event->noteConceptuelle->id,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }
}
