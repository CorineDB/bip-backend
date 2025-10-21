<?php

namespace App\Events;

use App\Models\Evaluation;
use App\Models\Projet;
use App\Models\Tdr;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TdrFaisabiliteEvalue implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Tdr $tdr;
    public Projet $projet;
    public Evaluation $evaluation;
    public User $evaluateur;
    public array $resultatsEvaluation;

    public function __construct(
        Tdr $tdr,
        Projet $projet,
        Evaluation $evaluation,
        User $evaluateur,
        array $resultatsEvaluation
    ) {
        $this->tdr = $tdr;
        $this->projet = $projet;
        $this->evaluation = $evaluation;
        $this->evaluateur = $evaluateur;
        $this->resultatsEvaluation = $resultatsEvaluation;
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
        return 'tdr.faisabilite.evalue';
    }

    public function broadcastWith(): array
    {
        return [
            'tdr_id' => $this->tdr->id,
            'projet_id' => $this->projet->id,
            'projet_titre' => $this->projet->titre_projet,
            'evaluation_id' => $this->evaluation->id,
            'evaluateur_id' => $this->evaluateur->id,
            'evaluateur_nom' => $this->evaluateur->name,
            'resultat_global' => $this->resultatsEvaluation['resultat_global'] ?? null,
            'date_evaluation' => now()->format('Y-m-d H:i:s'),
            'message' => 'Le TDR de faisabilité a été évalué',
        ];
    }
}
