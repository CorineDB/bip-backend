<?php

namespace App\Events;

use App\Models\Projet;
use App\Models\Tdr;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TdrFaisabiliteSoumis implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Tdr $tdr;
    public Projet $projet;
    public User $soumetteur;
    public bool $estResoumission;

    public function __construct(
        Tdr $tdr,
        Projet $projet,
        User $soumetteur,
        bool $estResoumission = false
    ) {
        $this->tdr = $tdr;
        $this->projet = $projet;
        $this->soumetteur = $soumetteur;
        $this->estResoumission = $estResoumission;
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('projet.' . $this->projet->id),
            new PrivateChannel('organisation.' . $this->projet->organisation_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'tdr.faisabilite.soumis';
    }

    public function broadcastWith(): array
    {
        return [
            'tdr_id' => $this->tdr->id,
            'projet_id' => $this->projet->id,
            'projet_titre' => $this->projet->titre_projet,
            'soumetteur_id' => $this->soumetteur->id,
            'soumetteur_nom' => $this->soumetteur->name,
            'est_resoumission' => $this->estResoumission,
            'date_soumission' => $this->tdr->date_soumission?->format('Y-m-d H:i:s'),
            'message' => $this->estResoumission
                ? 'Le TDR de faisabilité a été resoumis après révision'
                : 'Un nouveau TDR de faisabilité a été soumis',
        ];
    }
}
