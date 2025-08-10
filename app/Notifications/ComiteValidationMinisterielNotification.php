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

class ComiteValidationMinisterielNotification extends Notification implements ShouldQueue
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
            ->subject('Amendement/Commentaire requis - ' . $this->ideeProjet->sigle)
            ->line('L\'analyse multicritères de l\'idée de projet "' . $this->ideeProjet->sigle . '" est terminée.')
            ->line('Score AMC obtenu: ' . number_format($this->ideeProjet->score_amc ?? 0, 2))
            ->line('Score climatique: ' . number_format($this->ideeProjet->score_climatique ?? 0, 2))
            ->line('En tant que membre du Comité de validation ministériel, vous êtes invité(e) à examiner les résultats et apporter vos amendements ou commentaires.')
            ->action("Examiner et commenter", url("{$path}/idees/" . $this->ideeProjet->id . "/comite-validation"))
            ->line('Vos commentaires contribueront à l\'amélioration de l\'évaluation de cette idée de projet.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'comite_validation_ministeriel',
            'title' => 'Amendement/Commentaire requis',
            'body' => 'L\'AMC de "' . $this->ideeProjet->sigle . '" attend vos amendements/commentaires en tant que membre du Comité.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_amc_id' => $this->evaluationAMC->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'date_fin_amc' => now()->toISOString(),
                'action_requise' => 'amendement_commentaire_comite',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . '/comite-validation',
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'comite_validation_ministeriel',
            'title' => 'Amendement/Commentaire requis - Comité de validation',
            'message' => 'L\'analyse multicritères de "' . $this->ideeProjet->sigle . '" est terminée (Score AMC: ' . number_format($this->ideeProjet->score_amc ?? 0, 2) . '). En tant que membre du Comité de validation ministériel, vos amendements/commentaires sont requis.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'evaluation_amc_id' => $this->evaluationAMC->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'date_fin_amc' => now()->toISOString(),
                'action_requise' => 'amendement_commentaire_comite',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . '/comite-validation',
        ];
    }
}