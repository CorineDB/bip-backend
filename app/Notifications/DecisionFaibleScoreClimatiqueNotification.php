<?php

namespace App\Notifications;

use App\Models\IdeeProjet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DecisionFaibleScoreClimatiqueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected float $scoreClimatique;
    protected string $decision;
    protected ?string $commentaire;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, float $scoreClimatique, string $decision, ?string $commentaire = null)
    {
        $this->ideeProjet = $ideeProjet;
        $this->scoreClimatique = $scoreClimatique;
        $this->decision = $decision;
        $this->commentaire = $commentaire;
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
        $decisionText = $this->decision === 'refuser' ? 'refusée' : 'acceptée pour réévaluation';

        $message = (new MailMessage)
            ->subject('Décision suite au score climatique - ' . $this->ideeProjet->sigle)
            ->line('Le Responsable projet a pris une décision concernant l\'idée de projet "' . $this->ideeProjet->sigle . '".')
            ->line('Score climatique obtenu: ' . number_format($this->scoreClimatique, 2))
            ->line('Décision: Idée ' . $decisionText);

        if ($this->commentaire) {
            $message->line('Commentaire: ' . $this->commentaire);
        }

        if ($this->decision === 'reevaluer') {
            $message->line('Une nouvelle évaluation climatique est demandée.')
                   ->action("Commencer la réévaluation", url("{$path}/idees/" . $this->ideeProjet->id . "/validation-evaluation-climatique"));
        } else {
            $message->action("Voir les détails", url("{$path}/idees/" . $this->ideeProjet->id));
        }

        return $message->line('Merci pour votre contribution à l\'évaluation climatique.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'decision_faible_score_climatique',
            'title' => 'Décision suite au score climatique',
            'body' => 'Décision pour "' . $this->ideeProjet->sigle . '" (score: ' . number_format($this->scoreClimatique, 2) . '): ' . $this->decision,
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->scoreClimatique,
                'decision' => $this->decision,
                'commentaire' => $this->commentaire,
                'date_decision' => now()->toISOString(),
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . ($this->decision === 'reevaluer' ? '/details-evaluation-climatique' : ''),
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        $decisionText = $this->decision === 'refuser' ? 'refusée' : 'acceptée pour réévaluation';

        return [
            'type' => 'decision_faible_score_climatique',
            'title' => 'Décision suite au score climatique',
            'message' => 'Le Responsable projet a ' . $decisionText . ' l\'idée "' . $this->ideeProjet->sigle . '" (score: ' . number_format($this->scoreClimatique, 2) . ').' . ($this->commentaire ? ' Commentaire: ' . $this->commentaire : ''),
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->scoreClimatique,
                'decision' => $this->decision,
                'commentaire' => $this->commentaire,
                'date_decision' => now()->toISOString(),
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . ($this->decision === 'reevaluer' ? '/details-evaluation-climatique' : ''),
        ];
    }
}