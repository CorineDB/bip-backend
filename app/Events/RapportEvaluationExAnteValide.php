<?php

namespace App\Events;

use App\Models\Evaluation;
use App\Models\Projet;
use App\Models\Rapport;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RapportEvaluationExAnteValide implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Rapport $rapport;
    public Projet $projet;
    public Evaluation $evaluation;
    public User $validateur;
    public string $decision;

    public function __construct(
        Rapport $rapport,
        Projet $projet,
        Evaluation $evaluation,
        User $validateur,
        string $decision
    ) {
        $this->rapport = $rapport;
        $this->projet = $projet;
        $this->evaluation = $evaluation;
        $this->validateur = $validateur;
        $this->decision = $decision;
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
        return 'rapport.evaluation.ex.ante.valide';
    }

    public function broadcastWith(): array
    {
        return [
            'rapport_id' => $this->rapport->id,
            'projet_id' => $this->projet->id,
            'projet_titre' => $this->projet->titre_projet,
            'evaluation_id' => $this->evaluation->id,
            'validateur_id' => $this->validateur->id,
            'validateur_nom' => $this->validateur->name,
            'decision' => $this->decision,
            'date_validation' => now()->format('Y-m-d H:i:s'),
            'message' => 'Le rapport d\'évaluation ex-ante a été validé',
        ];
    }
}
