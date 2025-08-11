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

class FinAMCAnalysteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected Evaluation $evaluationAMC;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, Evaluation $evaluationAMC)
    {
        $this->ideeProjet = $ideeProjet;
        $this->evaluationAMC = $evaluationAMC;
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
            ->subject('Action requise - Validation idée projet à projet')
            ->line('L\'analyse multicritères de l\'idée de projet "' . $this->ideeProjet->sigle . '" est terminée.')
            ->line('Score AMC obtenu: ' . number_format($this->ideeProjet->score_amc ?? 0, 2))
            ->line('Score climatique: ' . number_format($this->ideeProjet->score_climatique ?? 0, 2))
            ->line('Vous devez maintenant effectuer la validation de l\'idée de projet à projet.')
            ->line('Le Comité de validation ministériel a également été notifié pour amendements ou commentaires.')
            ->action("Procéder à la validation", url("{$path}/idees/" . $this->ideeProjet->id . "/validation-projet"))
            ->line('Cette validation est nécessaire pour finaliser le processus d\'.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'fin_amc_analyste',
            'title' => 'Validation idée projet à projet requise',
            'body' => 'L\'AMC de "' . $this->ideeProjet->sigle . '" est terminée. Validation idée projet à projet requise.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_amc_id' => $this->evaluationAMC->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'date_fin_amc' => now()->toISOString(),
                'action_requise' => 'validation_idee_projet_a_projet',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . '/validation-projet',
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'fin_amc_analyste',
            'title' => 'Validation idée projet à projet requise',
            'message' => 'L\'analyse multicritères de "' . $this->ideeProjet->sigle . '" est terminée (Score AMC: ' . number_format($this->ideeProjet->score_amc ?? 0, 2) . '). Vous devez effectuer la validation de l\'idée de projet à projet.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_amc_id' => $this->evaluationAMC->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'date_fin_amc' => now()->toISOString(),
                'action_requise' => 'validation_idee_projet_a_projet',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . '/validation-projet',
        ];
    }
}