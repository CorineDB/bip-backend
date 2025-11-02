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

class ResultatAMCNotification extends Notification implements ShouldQueue
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
            ->subject('Résultat de l\'analyse multicritères - ' . $this->ideeProjet->sigle)
            ->line('L\'analyse multicritères de l\'idée de projet "' . $this->ideeProjet->sigle . '" a été complétée.')
            ->line('Score AMC obtenu: ' . number_format($this->ideeProjet->score_amc ?? 0, 2))
            ->line('Score climatique: ' . number_format($this->ideeProjet->score_climatique ?? 0, 2))
            ->line('Vous pouvez maintenant examiner les résultats et apporter des amendements ou commentaires si nécessaire.')
            ->action("Voir les résultats", url("{$path}/idees/" . $this->ideeProjet->hashed_id . "/details-analyse-multi-critere"))
            ->line('Les résultats sont disponibles pour consultation et amendement.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'resultat_amc',
            'title' => 'Résultat de l\'analyse multicritères',
            'body' => 'L\'AMC de "' . $this->ideeProjet->sigle . '" est terminée. Score AMC: ' . number_format($this->ideeProjet->score_amc ?? 0, 2),
            'data' => [
                'idee_projet_id' => $this->ideeProjet->hashed_id,
                'evaluation_amc_id' => $this->evaluationAMC->hashed_id,
                'sigle' => $this->ideeProjet->sigle,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'date_completion' => now()->toISOString(),
                'action_possible' => 'amendement_commentaire',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->hashed_id . '/details-analyse-multi-critere',
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'resultat_amc',
            'title' => 'Résultat de l\'analyse multicritères',
            'message' => 'L\'analyse multicritères de "' . $this->ideeProjet->sigle . '" est terminée. Score AMC: ' . number_format($this->ideeProjet->score_amc ?? 0, 2) . ', Score climatique: ' . number_format($this->ideeProjet->score_climatique ?? 0, 2) . '. Vous pouvez examiner et amender les résultats.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->hashed_id,
                'evaluation_amc_id' => $this->evaluationAMC->hashed_id,
                'sigle' => $this->ideeProjet->sigle,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'date_completion' => now()->toISOString(),
                'action_possible' => 'amendement_commentaire',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->hashed_id . '/details-analyse-multi-critere',
        ];
    }
}
