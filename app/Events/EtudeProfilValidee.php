<?php

namespace App\Events;

use App\Models\NoteConceptuelle;
use App\Models\Projet;
use App\Models\Evaluation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EtudeProfilValidee implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public NoteConceptuelle $noteConceptuelle;
    public Projet $projet;
    public Evaluation $evaluation;
    public User $validateur;
    public string $decision; // 'faire_etude_faisabilite_preliminaire', 'rejeter', etc.
    public ?string $commentaire;

    /**
     * Create a new event instance.
     */
    public function __construct(
        NoteConceptuelle $noteConceptuelle,
        Projet $projet,
        Evaluation $evaluation,
        User $validateur,
        string $decision,
        ?string $commentaire = null
    ) {
        $this->noteConceptuelle = $noteConceptuelle->load(['redacteur', 'champs']);
        $this->projet = $projet->load(['ministere']);
        $this->evaluation = $evaluation;
        $this->validateur = $validateur;
        $this->decision = $decision;
        $this->commentaire = $commentaire;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            // Canal global DPAF pour validations
            new PrivateChannel('dpaf.validations'),

            // Canaux hiérarchiques
            new PrivateChannel('ministeres.' . $this->projet->ministere_id . '.notes-conceptuelles.' . $this->noteConceptuelle->id),
            new PrivateChannel('ministeres.' . $this->projet->ministere_id . '.projets.' . $this->projet->id),
            new PrivateChannel('ministeres.' . $this->projet->ministere_id),

            // Canal organisation
            new PrivateChannel('organisations.' . $this->projet->organisation_id),

            // Canaux personnels
            new PrivateChannel('users.' . $this->noteConceptuelle->redacteur_id),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'etude-profil.validee';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'note_conceptuelle_id' => $this->noteConceptuelle->id,
            'projet_id' => $this->projet->id,
            'evaluation_id' => $this->evaluation->id,
            'decision' => $this->decision,
            'commentaire' => $this->commentaire,
            'validateur' => [
                'id' => $this->validateur->id,
                'nom' => $this->validateur->nom,
                'prenom' => $this->validateur->prenom,
            ],
            'note_intitule' => $this->noteConceptuelle->intitule,
            'projet_titre' => $this->projet->titre_projet,
            'ministere_nom' => $this->projet->ministere->nom ?? '',
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
