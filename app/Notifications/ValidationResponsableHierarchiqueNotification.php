<?php

namespace App\Notifications;

use App\Models\IdeeProjet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ValidationResponsableHierarchiqueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected string $decision;
    protected ?string $commentaire;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, string $decision, ?string $commentaire = null)
    {
        $this->ideeProjet = $ideeProjet;
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
        $decisionText = $this->decision === 'valider' ? 'validée' : 'refusée';

        if ($this->decision === 'valider') {
            $actionText = 'L\'idée va maintenant passer à l\'analyse multicritères.';
        } else {
            $actionText = 'L\'idée retourne à la phase de rédaction (statut: brouillon) pour révision et reformulation.';
        }

        $message = (new MailMessage)
            ->subject('Décision du Responsable hiérarchique - ' . $this->ideeProjet->sigle)
            ->line('Le Responsable hiérarchique a pris une décision concernant votre idée de projet "' . $this->ideeProjet->sigle . '".')
            ->line('Décision: Idée ' . $decisionText)
            ->line($actionText);

        if ($this->commentaire) {
            $message->line('Commentaire: ' . $this->commentaire);
        }

        if ($this->decision === 'valider') {
            $message->line('L\'analyste DGPD et les membres du Service technique/service étude ont été notifiés pour procéder à l\'analyse multicritères.');
        } else {
            $message->line('Veuillez réviser et reformuler votre idée en tenant compte des commentaires.')
                   ->action("Modifier l'idée", url("{$path}/idees/creer" . ($this->decision === 'refuser' ? '?edit=' . $this->ideeProjet->id : '')));
        }

        return $message;
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'validation_responsable_hierarchique',
            'title' => 'Décision du Responsable hiérarchique',
            'body' => 'Votre idée "' . $this->ideeProjet->sigle . '" a été ' . ($this->decision === 'valider' ? 'validée' : 'refusée') . ' par le Responsable hiérarchique.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'sigle' => $this->ideeProjet->sigle,
                'decision' => $this->decision,
                'commentaire' => $this->commentaire,
                'date_decision' => now()->toISOString(),
                'prochaine_etape' => $this->decision === 'valider' ? 'analyse_multicriteres' : 'retour_brouillon',
            ],
            'action_url' => '/idees/creer' . ($this->decision === 'refuser' ? '?edit=' . $this->ideeProjet->id : ''),
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        $decisionText = $this->decision === 'valider' ? 'validée' : 'refusée';
        $messageComplement = $this->decision === 'valider' ?
            ' L\'idée passe à l\'analyse multicritères.' :
            ' L\'idée retourne en phase de rédaction (brouillon) pour révision.';

        return [
            'type' => 'validation_responsable_hierarchique',
            'title' => 'Décision du Responsable hiérarchique',
            'message' => 'Votre idée de projet "' . $this->ideeProjet->sigle . '" a été ' . $decisionText . ' par le Responsable hiérarchique.' . $messageComplement . ($this->commentaire ? ' Commentaire: ' . $this->commentaire : ''),
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'sigle' => $this->ideeProjet->sigle,
                'decision' => $this->decision,
                'commentaire' => $this->commentaire,
                'date_decision' => now()->toISOString(),
                'prochaine_etape' => $this->decision === 'valider' ? 'analyse_multicriteres' : 'retour_brouillon',
            ],
            'action_url' => '/idees/creer' . ($this->decision === 'refuser' ? '?edit=' . $this->ideeProjet->id : ''),
        ];
    }
}