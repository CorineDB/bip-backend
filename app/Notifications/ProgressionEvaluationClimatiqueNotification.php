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

class ProgressionEvaluationClimatiqueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected Evaluation $evaluation;
    protected float $tauxProgression;
    protected ?float $scoreClimatique;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, Evaluation $evaluation, float $tauxProgression, ?float $scoreClimatique = null)
    {
        $this->ideeProjet = $ideeProjet;
        $this->evaluation = $evaluation;
        $this->tauxProgression = $tauxProgression;
        $this->scoreClimatique = $scoreClimatique;
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
            ->subject('Progression de l\'évaluation climatique')
            ->line('L\'évaluation climatique de l\'idée de projet "' . $this->ideeProjet->sigle . '" progresse.')
            ->line('Taux de progression: ' . number_format($this->tauxProgression, 1) . '%')
            ->when($this->scoreClimatique, function ($message) {
                return $message->line('Score climatique actuel: ' . number_format($this->scoreClimatique, 2));
            })
            ->action("Voir l'évaluation", url("{$path}/idees/" . $this->ideeProjet->hashed_id . "/details-evaluation-climatique"))
            ->line('Vous serez notifié dès que l\'évaluation sera terminée.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'evaluation_climatique_progression',
            'title' => 'Progression de l\'évaluation climatique',
            'body' => 'L\'évaluation de "' . $this->ideeProjet->sigle . '" progresse (' . number_format($this->tauxProgression, 1) . '%).',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->hashed_id,
                'evaluation_id' => $this->evaluation->hashed_id,
                'sigle' => $this->ideeProjet->sigle,
                'taux_progression' => $this->tauxProgression,
                'score_climatique_actuel' => $this->scoreClimatique,
                'date_mise_a_jour' => now()->toISOString(),
            ],
            'action_url' => '/idees/' . $this->ideeProjet->hashed_id . '/details-evaluation-climatique',
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'evaluation_climatique_progression',
            'title' => 'Progression de l\'évaluation climatique',
            'message' => 'L\'évaluation climatique de "' . $this->ideeProjet->sigle . '" progresse (' . number_format($this->tauxProgression, 1) . '%).',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->hashed_id,
                'evaluation_id' => $this->evaluation->hashed_id,
                'sigle' => $this->ideeProjet->sigle,
                'taux_progression' => $this->tauxProgression,
                'score_climatique_actuel' => $this->scoreClimatique,
                'date_mise_a_jour' => now()->toISOString(),
            ],
            'action_url' => '/idees/' . $this->ideeProjet->hashed_id . '/details-evaluation-climatique',
        ];
    }
}
