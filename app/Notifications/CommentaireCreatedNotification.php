<?php

namespace App\Notifications;

use App\Models\Commentaire;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class CommentaireCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $commentaire;

    /**
     * Create a new notification instance.
     */
    public function __construct(Commentaire $commentaire)
    {
        $this->commentaire = $commentaire;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Get the array representation for database storage.
     */
    public function toArray(object $notifiable): array
    {
        $commentateur = $this->commentaire->commentateur;
        $personne = $commentateur?->personne;
        $message = $personne
            ? $personne->nom . ' ' . $personne->prenom . ' a ajouté un commentaire'
            : 'Un nouveau commentaire a été ajouté';

        return [
            'type' => 'commentaire_created',
            'commentaire_id' => $this->commentaire->hashed_id,
            'commentaire' => $this->commentaire->commentaire,
            'commentateur' => [
                'id' => $commentateur->hashed_id ?? null,
                'nom' => $personne->nom ?? null,
                'prenom' => $personne->prenom ?? null,
            ],
            'ressource' => [
                'type' => class_basename($this->commentaire->commentaireable_type),
                'id' => $this->commentaire->commentaireable->hashed_id ?? null,
            ],
            'date' => $this->commentaire->date?->format('Y-m-d H:i:s'),
            'message' => $message,
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $commentateur = $this->commentaire->commentateur;
        $personne = $commentateur?->personne;
        $message = $personne
            ? $personne->nom . ' ' . $personne->prenom . ' a ajouté un commentaire'
            : 'Un nouveau commentaire a été ajouté';

        return new BroadcastMessage([
            'type' => 'commentaire_created',
            'commentaire_id' => $this->commentaire->hashed_id,
            'message' => $message,
            'commentaire' => $this->commentaire->commentaire,
            'commentateur' => [
                'id' => $commentateur->hashed_id ?? null,
                'nom' => $personne->nom ?? null,
                'prenom' => $personne->prenom ?? null,
            ],
            'ressource' => [
                'type' => class_basename($this->commentaire->commentaireable_type),
                'id' => $this->commentaire->commentaireable->hashed_id ?? null,
            ],
        ]);
    }
}
