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

class EvaluationClimatiqueFinaliseeNotification extends Notification implements ShouldQueue
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
        return ['database', 'broadcast', 'mail'];
    }

    public function toMail($notifiable)
    {
        $path = env("CLIENT_APP_URL") ?? config("app.url");

        return (new MailMessage)
            ->subject('Évaluation climatique terminée')
            ->line('L\'évaluation climatique de l\'idée de projet "' . $this->ideeProjet->sigle . '" a été terminée.')
            ->line('Score climatique obtenu: ' . ($this->ideeProjet->score_climatique ?? 'Non calculé'))
            ->action("Voir les résultats", url("{$path}/idees/" . $this->ideeProjet->id))
            ->line('Vous pouvez maintenant procéder à la validation de cette idée de projet.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'evaluation_climatique_terminee',
            'title' => 'Évaluation climatique terminée',
            'body' => 'L\'évaluation climatique de l\'idée de projet "' . $this->ideeProjet->sigle . '" a été terminée avec un score de ' . ($this->ideeProjet->score_climatique ?? 'N/A') . '.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_id' => $this->evaluation->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->ideeProjet->score_climatique,
                'date_fin_evaluation' => $this->evaluation->valider_le?->toISOString(),
                'statut' => $this->evaluation->getStatutTextAttribute(),
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id,
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'evaluation_climatique_terminee',
            'title' => 'Évaluation climatique terminée',
            'message' => 'L\'évaluation climatique de l\'idée de projet "' . $this->ideeProjet->sigle . '" a été terminée avec un score de ' . ($this->ideeProjet->score_climatique ?? 'N/A') . '.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_id' => $this->evaluation->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->ideeProjet->score_climatique,
                'date_fin_evaluation' => $this->evaluation->valider_le?->toISOString(),
                'statut' => $this->evaluation->getStatutTextAttribute(),
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id,
        ];
    }
}