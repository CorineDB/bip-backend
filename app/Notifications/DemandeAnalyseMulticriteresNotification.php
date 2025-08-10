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

class DemandeAnalyseMulticriteresNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected User $responsableValidateur;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, User $responsableValidateur)
    {
        $this->ideeProjet = $ideeProjet;
        $this->responsableValidateur = $responsableValidateur;
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
            ->subject('Nouvelle analyse multicritères requise - ' . $this->ideeProjet->sigle)
            ->line('L\'idée de projet "' . $this->ideeProjet->sigle . '" a été validée par le Responsable hiérarchique.')
            ->line('Score climatique obtenu: ' . number_format($this->ideeProjet->score_climatique ?? 0, 2))
            ->line('Validée par: ' . ($this->responsableValidateur->personne->nom ?? 'Non défini') . ' ' . ($this->responsableValidateur->personne->prenom ?? ''))
            ->line('Email du validateur: ' . $this->responsableValidateur->email)
            ->line('Cette idée doit maintenant faire l\'objet d\'une analyse multicritères.')
            ->action("Commencer l'analyse", url("{$path}/idees/" . $this->ideeProjet->id . "/amc"))
            ->line('Veuillez procéder à l\'analyse multicritères de cette idée de projet.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'demande_analyse_multicriteres',
            'title' => 'Nouvelle analyse multicritères',
            'body' => 'L\'idée "' . $this->ideeProjet->sigle . '" validée par ' . ($this->responsableValidateur->personne->nom ?? 'Responsable') . ' nécessite une analyse multicritères.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'validateur_nom' => $this->responsableValidateur->personne->nom ?? '',
                'validateur_prenom' => $this->responsableValidateur->personne->prenom ?? '',
                'validateur_email' => $this->responsableValidateur->email,
                'date_validation' => now()->toISOString(),
                'action_requise' => 'analyse_multicriteres',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . '/amc',
        ]);
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'demande_analyse_multicriteres',
            'title' => 'Nouvelle analyse multicritères requise',
            'message' => 'L\'idée de projet "' . $this->ideeProjet->sigle . '" (score climatique: ' . number_format($this->ideeProjet->score_climatique ?? 0, 2) . ') a été validée par ' . ($this->responsableValidateur->personne->nom ?? '') . ' ' . ($this->responsableValidateur->personne->prenom ?? '') . ' et nécessite une analyse multicritères.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->ideeProjet->score_climatique ?? 0,
                'validateur_nom' => $this->responsableValidateur->personne->nom ?? '',
                'validateur_prenom' => $this->responsableValidateur->personne->prenom ?? '',
                'validateur_email' => $this->responsableValidateur->email,
                'date_validation' => now()->toISOString(),
                'action_requise' => 'analyse_multicriteres',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->id . '/amc',
        ];
    }
}