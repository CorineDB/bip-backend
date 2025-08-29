<?php

namespace App\Http\Resources\projets;

use App\Http\Resources\BaseApiResource;
use App\Http\Resources\NoteConceptuelleResource;
use App\Http\Resources\SecteurResourcePublic;
use App\Http\Resources\TdrResource;
use Illuminate\Http\Request;

class ProjetsResource extends BaseApiResource
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
            // Identifiants et métadonnées
            'identifiant_bip' => $this->identifiant_bip,
            'identifiant_sigfp' => $this->identifiant_sigfp,
            'sigle' => $this->sigle,
            'titre_projet' => $this->titre_projet,
            //'porteur_projet' => $this->porteur_projet,

            // Statuts et phases
            'statut' => $this->statut?->value ?? $this->statut,
            'phase' => $this->phase?->value ?? $this->phase,
            'sous_phase' => $this->sous_phase?->value ?? $this->sous_phase,
            'type_projet' => $this->type_projet?->value ?? $this->type_projet,

            // Scores d'évaluation
            'score_climatique' => $this->score_climatique,
            'score_amc' => $this->score_amc,

            // Descriptions et contenus principaux
            'description_projet' => $this->description_projet,

            // Détails techniques et organisationnels
            'duree' => $this->duree,
            // Données JSON structurées
            'cout_estimatif_projet' => $this->cout_estimatif_projet ?? [],

            'secteur' => new SecteurResourcePublic($this->secteur),

            'noteConceptuelle' => new NoteConceptuelleResource($this->noteConceptuelle),

            // TDRs
            'tdr_prefaisabilite' => $this->whenLoaded('tdrPrefaisabilite', function() {
                return $this->tdrPrefaisabilite->first() ? new TdrResource($this->tdrPrefaisabilite->first()) : null;
            }),
            'tdr_faisabilite' => $this->whenLoaded('tdrFaisabilite', function() {
                return $this->tdrFaisabilite->first() ? new TdrResource($this->tdrFaisabilite->first()) : null;
            })
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
                'type' => 'projet',
                'version' => '1.0',
            ],
        ]);
    }
}
