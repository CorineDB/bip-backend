<?php

namespace App\Notifications;

use App\Models\IdeeProjet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Broadcasting\PrivateChannel;

class NouvelleIdeeProjetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected IdeeProjet $ideeProjet;
    protected float $scoreClimatique;

    /**
     * Create a new notification instance.
     */
    public function __construct(IdeeProjet $ideeProjet, float $scoreClimatique)
    {
        $this->ideeProjet = $ideeProjet;
        $this->scoreClimatique = $scoreClimatique;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'mail', 'broadcast'];
    }

    public function toMail($notifiable)
    {
        $path = env("CLIENT_APP_URL") ?? config("app.url");

        return (new MailMessage)
            ->subject('Nouvelle idée de projet à valider')
            ->line('Une nouvelle idée de projet "' . $this->ideeProjet->sigle . '" a été soumise pour validation.')
            ->line('Score climatique obtenu: ' . number_format($this->scoreClimatique, 2))
            ->line('Responsable du projet: ' . ($this->ideeProjet->responsable->personne->nom ?? 'Non défini') . ' ' . ($this->ideeProjet->responsable->personne->prenom ?? ''))
            ->line('Cette idée nécessite votre validation avant de passer à l\'étape suivante.')
            ->action("Examiner l'idée", url("{$path}/idees/" . $this->ideeProjet->hashed_id))
            ->line('Veuillez examiner l\'idée et donner votre décision de validation.');
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'type' => 'nouvelle_idee_projet',
            'title' => 'Nouvelle idée de projet',
            'body' => 'L\'idée de projet "' . $this->ideeProjet->sigle . '" (score climatique: ' . number_format($this->scoreClimatique, 2) . ') attend votre validation.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->hashed_id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->scoreClimatique,
                'responsable_nom' => $this->ideeProjet->responsable->personne->nom ?? '',
                'responsable_prenom' => $this->ideeProjet->responsable->personne->prenom ?? '',
                'date_soumission' => now()->toISOString(),
                'action_requise' => 'validation_hierarchique',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->hashed_id
        ]);
    }

    public function broadcastOn()
    {
        return [
            new PrivateChannel('idee.de.projet.creer.' . $this->ideeProjet->hashed_id),
            new PrivateChannel('App.Models.User.' . $this->ideeProjet->responsableId)
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'nouvelle_idee_projet',
            'title' => 'Nouvelle idée de projet à valider',
            'message' => 'L\'idée de projet "' . $this->ideeProjet->sigle . '" a été soumise par ' . ($this->ideeProjet->responsable->personne->nom ?? '') . ' avec un score climatique de ' . number_format($this->scoreClimatique, 2) . '. Validation requise.',
            'data' => [
                'idee_projet_id' => $this->ideeProjet->hashed_id,
                'sigle' => $this->ideeProjet->sigle,
                'score_climatique' => $this->scoreClimatique,
                'responsable_nom' => $this->ideeProjet->responsable->personne->nom ?? '',
                'responsable_prenom' => $this->ideeProjet->responsable->personne->prenom ?? '',
                'date_soumission' => now()->toISOString(),
                'action_requise' => 'validation_hierarchique',
            ],
            'action_url' => '/idees/' . $this->ideeProjet->hashed_id
        ];
    }
}
