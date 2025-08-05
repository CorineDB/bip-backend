<?php

namespace App\Events;

use App\Models\IdeeProjet;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IdeeProjetCree
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public IdeeProjet $ideeProjet;

    /**
     * Create a new event instance.
     */
    public function __construct(IdeeProjet $ideeProjet)
    {
        $this->ideeProjet = $ideeProjet;
    }

    /**
     * Get the channel the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('idee-projet.' . $this->ideeProjet->id),
            new PrivateChannel('idee-projet.' . $this->ideeProjet->id)
        ]; // ou autre canal
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    /* public function broadcastAs()
    {
        return 'idee-de-projet.creer';
    } */

    /**
     * Get the data to broadcast.
     *
     * @return array<string, mixed>
     */
    /* public function broadcastWith(): array
    {
        return ['id' => $this->ideeProjet->id, 'titre_projet' => $this->ideeProjet->titre_projet, 'cout_estimatif_projet' => $this->ideeProjet->cout_estimatif_projet, 'duree' => $this->ideeProjet->duree, 'description_projet' => $this->ideeProjet->description_projet, 'objectif_general' => $this->ideeProjet->objectif_general, 'phase' => $this->ideeProjet->phase];
    } */

    /**
     * Determine if this event should broadcast.
     */
    /*public function broadcastWhen(): bool
    {
        return $this->ideeProjet->value > 100;
    }*/
}
