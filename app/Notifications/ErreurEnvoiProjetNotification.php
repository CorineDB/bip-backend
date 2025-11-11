<?php

namespace App\Notifications;

use App\Models\Projet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ErreurEnvoiProjetNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $projet;
    protected string $errorMessage;
    protected int $attempts;

    /**
     * Create a new notification instance.
     */
    public function __construct($projet, string $errorMessage, int $attempts)
    {
        $this->projet = $projet;
        $this->errorMessage = $errorMessage;
        $this->attempts = $attempts;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $projetTitre = $this->projet ? $this->projet->titre : 'Projet inconnu';
        $projetId = $this->projet ? $this->projet->hashed_id : 'N/A';

        return (new MailMessage)
            ->error()
            ->subject('Échec d\'envoi d\'un projet mature vers SIGFP')
            ->greeting('Bonjour,')
            ->line('Une erreur est survenue lors de l\'envoi d\'un projet mature vers le système externe SIGFP.')
            ->line('**Détails du projet :**')
            ->line('- Titre : ' . $projetTitre)
            ->line('- ID : ' . $projetId)
            ->line('- Nombre de tentatives : ' . $this->attempts)
            ->line('')
            ->line('**Message d\'erreur :**')
            ->line($this->errorMessage)
            ->line('')
            ->line('L\'équipe technique a été notifiée et va investiguer le problème.')
            ->action('Voir le projet', config('app.client_app_url') . '/projets/' . $projetId)
            ->line('Merci de votre attention.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'projet_id' => $this->projet ? $this->projet->hashed_id : null,
            'projet_titre' => $this->projet ? $this->projet->titre : null,
            'error_message' => $this->errorMessage,
            'attempts' => $this->attempts,
            'type' => 'erreur_envoi_projet_sigfp'
        ];
    }
}
