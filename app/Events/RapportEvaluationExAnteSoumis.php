<?php

namespace App\Events;

use App\Models\Projet;
use App\Models\Rapport;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RapportEvaluationExAnteSoumis implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Rapport $rapport;
    public Projet $projet;
    public User $soumetteur;
    public bool $estResoumission;

    public function __construct(
        Rapport $rapport,
        Projet $projet,
        User $soumetteur,
        bool $estResoumission = false
    ) {
        $this->rapport = $rapport;
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
        return 'rapport.evaluation.ex.ante.soumis';
    }

    public function broadcastWith(): array
    {
        return [
            'rapport_id' => $this->rapport->id,
            'projet_id' => $this->projet->id,
            'projet_titre' => $this->projet->titre_projet,
            'soumetteur_id' => $this->soumetteur->id,
            'soumetteur_nom' => $this->soumetteur->name,
            'est_resoumission' => $this->estResoumission,
            'date_soumission' => $this->rapport->date_soumission?->format('Y-m-d H:i:s'),
            'message' => $this->estResoumission
                ? 'Le rapport d\'évaluation ex-ante a été resoumis après corrections'
                : 'Un nouveau rapport d\'évaluation ex-ante a été soumis',
        ];
    }
}
