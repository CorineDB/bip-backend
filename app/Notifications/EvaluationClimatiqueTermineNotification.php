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

class EvaluationClimatiqueTermineNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected Evaluation $evaluation;
    protected float $scoreClimatique;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, Evaluation $evaluation, float $scoreClimatique)
    {
        $this->ideeProjet = $ideeProjet;
        $this->evaluation = $evaluation;
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
            ->subject('Évaluation climatique complétée - Action requise')
            ->line('L\'évaluation climatique de l\'idée de projet "' . $this->ideeProjet->sigle . '" est maintenant complétée.')
            ->line('Score climatique obtenu: ' . number_format($this->scoreClimatique, 2))
            ->line('Vous devez maintenant valider ce score et transmettre l\'idée au Responsable hiérarchique.')
            ->action("Finaliser l'évaluation", url("{$path}/idees/" . $this->ideeProjet->id . "/finaliser"))
            ->line('Cette action est nécessaire pour poursuivre le processus d\'évaluation.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'evaluation_climatique_termine',
            'title' => 'Évaluation climatique complétée',
            'body' => 'Tous les évaluateurs ont terminé l\'évaluation de "' . $this->ideeProjet->sigle . '". Score: ' . number_format($this->scoreClimatique, 2) . '. Action requise.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_id' => $this->evaluation->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->scoreClimatique,
                'date_completion' => now()->toISOString(),
                'action_requise' => 'finaliser_score_climatique',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . '/finaliser',
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'evaluation_climatique_termine',
            'title' => 'Évaluation climatique complétée',
            'message' => 'Tous les évaluateurs ont terminé l\'évaluation climatique de "' . $this->ideeProjet->sigle . '". Score obtenu: ' . number_format($this->scoreClimatique, 2) . '. Veuillez finaliser et transmettre au Responsable hiérarchique.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_id' => $this->evaluation->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->scoreClimatique,
                'date_completion' => now()->toISOString(),
                'action_requise' => 'finaliser_score_climatique',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . '/finaliser',
        ];
    }
}