<?php

namespace App\Notifications;

use App\Models\IdeeProjet;
use App\Models\Evaluation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationClimatiqueAssigneeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected Evaluation $evaluation;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, Evaluation $evaluation)
    {
        $this->ideeProjet = $ideeProjet;
        $this->evaluation = $evaluation;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', /* 'broadcast', */ 'mail'];
    }

    public function toMail($notifiable)
    {
        $path = env("CLIENT_APP_URL") ?? config("app.url");

        return (new MailMessage)
            ->subject('Nouvelle idée de projet créée')
            ->line('Vous avez été assigné(e) pour effectuer l\'évaluation climatique de l\'idée de projet "' . $this->ideeProjet->sigle . '".')
            ->action("Accedez a l'objet de l'evaluation", url("{$path}/idees-projet/" . $this->ideeProjet->id));
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'evaluation_climatique_assignee',
            'title' => 'Évaluation climatique assignée',
            'body' => 'Vous avez été assigné(e) pour effectuer l\'évaluation climatique de l\'idée de projet "' . $this->ideeProjet->sigle . '".',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_id' => $this->evaluation->id,
                'sigle' => $this->ideeProjet->sigle,
                'date_debut_evaluation' => $this->evaluation->date_debut_evaluation->toISOString(),
            ],
            'action_url' => '/evaluations/climatique/' . $this->ideeProjet->id,
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'evaluation_climatique_assignee',
            'title' => 'Évaluation climatique assignée',
            'message' => 'Vous avez été assigné(e) pour effectuer l\'évaluation climatique de l\'idée de projet "' . $this->ideeProjet->sigle . '".',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_id' => $this->evaluation->id,
                'sigle' => $this->ideeProjet->sigle,
                'date_debut_evaluation' => $this->evaluation->date_debut_evaluation->toISOString(),
            ],
            'action_url' => '/evaluations/climatique/' . $this->ideeProjet->id,
        ];
    }
}