<?php

namespace App\Events;

use App\Models\IdeeProjet;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IdeeProjetTransformee
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
}