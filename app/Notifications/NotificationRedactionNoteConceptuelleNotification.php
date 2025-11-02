<?php

namespace App\Notifications;

use App\Models\IdeeProjet;
use App\Models\Projet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationRedactionNoteConceptuelleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected ?Projet $projet;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, ?Projet $projet = null)
    {
        $this->ideeProjet = $ideeProjet;
        $this->projet = $projet;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable)
    {
        $path = env("CLIENT_APP_URL") ?? config("app.url");

        return (new MailMessage)
            ->subject('Rédaction de note conceptuelle requise - ' . $this->ideeProjet->sigle)
            ->line('L\'idée de projet "' . $this->ideeProjet->sigle . '" a été validée et transformée en projet.')
            ->line('Score AMC final: ' . number_format($this->ideeProjet->score_amc ?? 0, 2))
            ->line('Score climatique: ' . number_format($this->ideeProjet->score_climatique ?? 0, 2))
            ->line('Vous devez maintenant procéder à la rédaction de la note conceptuelle de ce projet.')
            ->when($this->projet, function ($message) {
                return $message->line('ID du projet créé: ' . $this->projet->hashed_id);
            })
            ->action("Rédiger la note conceptuelle", url("{$path}/projets/" . ($this->projet->hashed_id ?? $this->ideeProjet->hashed_id) . "/note-conceptuelle"))
            ->line('Cette étape est cruciale pour la suite du développement du projet.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'redaction_note_conceptuelle',
            'title' => 'Rédaction de note conceptuelle',
            'body' => 'Le projet "' . $this->ideeProjet->sigle . '" nécessite la rédaction d\'une note conceptuelle.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->hashed_id,
                'projet_id' => $this->projet->hashed_id ?? null,
                'sigle' => $this->ideeProjet->sigle,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'date_validation' => now()->toISOString(),
                'action_requise' => 'redaction_note_conceptuelle',
            ],
            'action_url' => '/projets/' . ($this->projet->hashed_id ?? $this->ideeProjet->hashed_id) . '/note-conceptuelle',
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'redaction_note_conceptuelle',
            'title' => 'Rédaction de note conceptuelle requise',
            'message' => 'L\'idée "' . $this->ideeProjet->sigle . '" a été validée et transformée en projet (Scores: AMC ' . number_format($this->ideeProjet->score_amc ?? 0, 2) . ', Climatique ' . number_format($this->ideeProjet->score_climatique ?? 0, 2) . '). Rédaction de la note conceptuelle requise.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->hashed_id,
                'projet_id' => $this->projet->hashed_id ?? null,
                'sigle' => $this->ideeProjet->sigle,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'date_validation' => now()->toISOString(),
                'action_requise' => 'redaction_note_conceptuelle',
            ],
            'action_url' => '/projets/' . ($this->projet->hashed_id ?? $this->ideeProjet->hashed_id) . '/note-conceptuelle',
        ];
    }
}
