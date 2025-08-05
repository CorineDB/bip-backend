<?php

namespace App\Listeners;

use App\Events\IdeeProjetCree;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Notifications\IdeeProjetCreeNotification;

class NotifierIdeeProjetSoumise implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(IdeeProjetCree $event): void
    {
        $ideeProjet = $event->ideeProjet;

        /*Notification::route('mail', $event->idee->user->email)
            ->notify(new IdeeSoumiseNotification($event->idee));*/

        $event->ideeProjet->responsable->notify(new IdeeProjetCreeNotification($ideeProjet));

        // Envoyer la notification in-app
        //Notification::send($usersToNotify, new IdeeProjetCreeNotification($ideeProjet));
    }
}