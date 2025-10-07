<?php

namespace App\Http\Resources;

use App\Http\Resources\projets\ProjetsResource;
use Illuminate\Http\Request;

class ListNoteConceptuelleResource extends BaseApiResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'intitule' => $this->intitule,
            'numero_contrat' => $this->numero_contrat,
            'numero_dossier' => $this->numero_dossier,
            'accept_term' => $this->accept_term,
            'statut' => $this->statut,
            'statut_libelle' => match($this->statut) {
                1 => 'Soumise',
                default => 'Brouillon'
            },

            'valider_par' => $this->validateur ? new UserResource($this->validateur) : null,
            'rediger_par' => $this->redacteur ? new UserResource($this->redacteur) : null,
            'note_conceptuelle' => $this->note_conceptuelle,
            'decision' => $this->decision,
            'fichiers' => $this->whenLoaded('fichiers', function() {
                return FichierResource::collection($this->fichiers->sortBy('ordre'));
            }),
            'parent' => $this->whenLoaded("parent", function(){
                return new NoteConceptuelleResource($this->parent->load("parent"));
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @param Request $request
     * @return array
     */
    public function with(Request $request): array
    {
        return array_merge(parent::with($request), [
            'meta' => [
                'type' => 'noteconceptuelle',
                'version' => '1.0',
            ],
        ]);
    }
}
