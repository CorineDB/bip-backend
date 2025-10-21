<?php

namespace App\Events;

use App\Models\NoteConceptuelle;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NoteConceptuelleSoumise implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public NoteConceptuelle $noteConceptuelle;
    public Projet $projet;
    public User $acteur;
    public ?string $ancienStatut;
    public string $nouveauStatut;

    /**
     * Create a new event instance.
     */
    public function __construct(
        NoteConceptuelle $noteConceptuelle,
        Projet $projet,
        User $acteur,
        ?string $ancienStatut = null,
        string $nouveauStatut = 'soumise'
    ) {
        $this->noteConceptuelle = $noteConceptuelle->load(['redacteur', 'champs']);
        $this->projet = $projet->load(['ministere', 'organisation']);
        $this->acteur = $acteur;
        $this->ancienStatut = $ancienStatut;
        $this->nouveauStatut = $nouveauStatut;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
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
        return 'note-conceptuelle.soumise';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'note_conceptuelle_id' => $this->noteConceptuelle->id,
            'projet_id' => $this->projet->id,
            'intitule' => $this->noteConceptuelle->intitule,
            'acteur' => [
                'id' => $this->acteur->id,
                'nom' => $this->acteur->nom,
                'prenom' => $this->acteur->prenom,
            ],
            'ancien_statut' => $this->ancienStatut,
            'nouveau_statut' => $this->nouveauStatut,
            'projet_titre' => $this->projet->titre_projet,
            'ministere_nom' => $this->projet->ministere->nom ?? '',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
