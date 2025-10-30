<?php

namespace App\Events;

use App\Models\Commentaire;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentaireCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $commentaire;

    /**
     * Create a new event instance.
     */
    public function __construct(Commentaire $commentaire)
    {
        $this->commentaire = $commentaire->load(['commentateur', 'fichiers']);
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Broadcaster sur un channel privé de la ressource commentée
        $type = class_basename($this->commentaire->commentaireable_type);
        return [
            new PrivateChannel('commentaires.' . $type . '.' . $this->commentaire->commentaireable_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'commentaire.created';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->commentaire->id,//->hashed_id,
            'commentaire' => $this->commentaire->commentaire,
            'commentateur' => [
                'id' => $this->commentaire->commentateur->id ?? null,//->hashed_id ?? null,
                'nom' => $this->commentaire->commentateur->personne->nom ?? null,
                'prenom' => $this->commentaire->commentateur->personne->prenom ?? null,
            ],
            'date' => $this->commentaire->date?->format('Y-m-d H:i:s'),
            'commentaireable_type' => class_basename($this->commentaire->commentaireable_type),
            'commentaireable_id' => $this->commentaire->commentaireable->id ?? null,
            'nb_fichiers' => $this->commentaire->fichiers->count(),
        ];
    }
}
