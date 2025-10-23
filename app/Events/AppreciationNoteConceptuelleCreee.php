<?php

namespace App\Events;

use App\Models\NoteConceptuelle;
use App\Models\Evaluation;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppreciationNoteConceptuelleCreee implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Evaluation $evaluation;
    public NoteConceptuelle $noteConceptuelle;
    public Projet $projet;
    public User $evaluateur;
    public string $typeAppreciation;
    public ?string $statut;

    /**
     * Create a new event instance.
     */
    public function __construct(
        Evaluation $evaluation,
        NoteConceptuelle $noteConceptuelle,
        Projet $projet,
        User $evaluateur,
        string $typeAppreciation = 'note-conceptuelle',
        ?string $statut = null
    ) {
        $this->evaluation = $evaluation->load(['evaluationCriteres', 'evaluateur']);
        $this->noteConceptuelle = $noteConceptuelle->load(['redacteur', 'champs']);
        $this->projet = $projet->load(['ministere']);
        $this->evaluateur = $evaluateur;
        $this->typeAppreciation = $typeAppreciation;
        $this->statut = $statut ?? $evaluation->statut;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('evaluations.' . $this->evaluation->id),
            new PrivateChannel('notes-conceptuelles.' . $this->noteConceptuelle->id),
            new PrivateChannel('projets.' . $this->projet->id),
            new PrivateChannel('ministeres.' . ($this->projet->ministere->id ?? 'unknown')),
            new PrivateChannel('users.' . $this->noteConceptuelle->redacteur_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'appreciation.note-conceptuelle.creee';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'evaluation_id' => $this->evaluation->id,
            'note_conceptuelle_id' => $this->noteConceptuelle->id,
            'projet_id' => $this->projet->id,
            'type_appreciation' => $this->typeAppreciation,
            'statut' => $this->statut,
            'evaluateur' => [
                'id' => $this->evaluateur->id,
                'nom' => $this->evaluateur->nom,
                'prenom' => $this->evaluateur->prenom,
            ],
            'note_details' => [
                'intitule' => $this->noteConceptuelle->intitule,
                'redacteur' => [
                    'nom' => $this->noteConceptuelle->redacteur->nom ?? '',
                    'prenom' => $this->noteConceptuelle->redacteur->prenom ?? '',
                ],
            ],
            'projet_titre' => $this->projet->titre_projet,
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
