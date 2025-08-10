<?php

namespace App\Notifications;

use App\Models\IdeeProjet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DecisionFinaleValidationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected string $decision;
    protected ?string $commentaire;
    protected User $analysteValidateur;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, string $decision, User $analysteValidateur, ?string $commentaire = null)
    {
        $this->ideeProjet = $ideeProjet;
        $this->decision = $decision;
        $this->analysteValidateur = $analysteValidateur;
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
        $decisionText = $this->decision === 'valider' ? 'validée et transformée en projet' : 'refusée';
        
        $message = (new MailMessage)
            ->subject('Décision finale - ' . $this->ideeProjet->sigle)
            ->line('L\'analyste DGPD a pris une décision finale concernant l\'idée de projet "' . $this->ideeProjet->sigle . '".')
            ->line('Décision: Idée ' . $decisionText)
            ->line('Score AMC: ' . number_format($this->ideeProjet->score_amc ?? 0, 2))
            ->line('Score climatique: ' . number_format($this->ideeProjet->score_climatique ?? 0, 2))
            ->line('Validée par: ' . ($this->analysteValidateur->personne->nom ?? '') . ' ' . ($this->analysteValidateur->personne->prenom ?? ''));

        if ($this->commentaire) {
            $message->line('Commentaire: ' . $this->commentaire);
        }

        if ($this->decision === 'valider') {
            $message->line('La DPAF a été notifiée pour la rédaction de la note conceptuelle du projet.');
        }

        return $message->action("Voir les détails", url("{$path}/idees/" . $this->ideeProjet->id));
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'decision_finale_validation',
            'title' => 'Décision finale de validation',
            'body' => 'L\'idée "' . $this->ideeProjet->sigle . '" a été ' . ($this->decision === 'valider' ? 'validée et transformée en projet' : 'refusée') . ' par l\'analyste DGPD.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'sigle' => $this->ideeProjet->sigle,
                'decision' => $this->decision,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'analyste_nom' => $this->analysteValidateur->personne->nom ?? '',
                'analyste_prenom' => $this->analysteValidateur->personne->prenom ?? '',
                'commentaire' => $this->commentaire,
                'date_decision' => now()->toISOString(),
                'prochaine_etape' => $this->decision === 'valider' ? 'note_conceptuelle' : 'termine',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id,
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        $decisionText = $this->decision === 'valider' ? 'validée et transformée en projet' : 'refusée';
        $messageComplement = $this->decision === 'valider' ? 
            ' La DPAF a été notifiée pour rédiger la note conceptuelle.' : 
            '';
        
        return [
            'type' => 'decision_finale_validation',
            'title' => 'Décision finale de validation',
            'message' => 'L\'idée de projet "' . $this->ideeProjet->sigle . '" a été ' . $decisionText . ' par l\'analyste DGPD (' . ($this->analysteValidateur->personne->nom ?? '') . ').' . $messageComplement . ($this->commentaire ? ' Commentaire: ' . $this->commentaire : ''),
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'sigle' => $this->ideeProjet->sigle,
                'decision' => $this->decision,
                'score_amc' => $this->ideeProjet->score_amc ?? 0,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'analyste_nom' => $this->analysteValidateur->personne->nom ?? '',
                'analyste_prenom' => $this->analysteValidateur->personne->prenom ?? '',
                'commentaire' => $this->commentaire,
                'date_decision' => now()->toISOString(),
                'prochaine_etape' => $this->decision === 'valider' ? 'note_conceptuelle' : 'termine',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id,
        ];
    }
}