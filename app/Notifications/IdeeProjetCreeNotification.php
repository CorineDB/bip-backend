<?php

namespace App\Notifications;

use App\Models\IdeeProjet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IdeeProjetCreeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet)
    {
        $this->ideeProjet = $ideeProjet;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail'/* , 'broadcast' */];
    }

    public function toMail($notifiable)
    {
        $path = env("CLIENT_APP_URL") ?? config("app.url");
        return (new MailMessage)
            ->subject('Nouvelle idée de projet créée')
            ->line("Votre idée a été bien enregistrée.")
            ->action('Lire le message', url("{$path}/idees/" . $this->ideeProjet->id));
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'idee_id' => $this->ideeProjet->id,
            'titre' => $this->ideeProjet->titre_projet,
            'message' => "Une idée a été soumise : {$this->ideeProjet->titre_projet}",
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'idee_projet_cree',
            'title' => 'Nouvelle idée de projet créée',
            'message' => 'Une nouvelle idée de projet "' . $this->ideeProjet->titre_projet . '" a été créée et nécessite votre attention.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'sigle' => $this->ideeProjet->sigle,
                'created_at' => $this->ideeProjet->created_at->toISOString(),
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id,
        ];
    }
}