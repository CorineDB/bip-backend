<?php

namespace App\Http\Resources\idees_projet;

use App\Http\Resources\BaseApiResource;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IdeesProjetResource extends BaseApiResource
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
            'id' => $this->hashed_id,
            // Identifiants et métadonnées
            'identifiant_bip' => $this->identifiant_bip,
            'identifiant_sigfp' => $this->identifiant_sigfp,
            'sigle' => $this->sigle,
            'titre_projet' => $this->titre_projet,
            'porteur_projet' => $this->porteur_projet,
            'est_un_projet' => $this->projet ? true : false,
            'projetId' => $this->when($this->projet, function(){
                return $this->projet->hashed_id;
            }),

            // Statuts et phases
            'statut' => $this->statut?->value ?? $this->statut,
            // Statuts et phases
            'statut' => $this->statut?->value ?? $this->statut,
            'phase' => $this->phase?->value ?? $this->phase,
            'sous_phase' => $this->sous_phase?->value ?? $this->sous_phase,
            'type_projet' => $this->type_projet?->value ?? $this->type_projet,
            'est_coherent' => $this->est_coherent,
            'est_soumise' => $this->est_soumise,

            // Scores d'évaluation
            'score_climatique' => $this->score_climatique,
            'score_amc' => $this->score_amc,
            'score_pertinence' => $this->score_pertinence,

            // Descriptions et contenus principaux
            'description_projet' => $this->description_projet,
            // Détails techniques et organisationnels
            'duree' => $this->duree,
            // Données JSON structurées
            'cout_estimatif_projet' => $this->cout_estimatif_projet ?? [],
            'created_at' => Carbon::parse($this->created_at)->format("Y-m-d H:i:s"),
            'updated_at' => Carbon::parse($this->updated_at)->format("Y-m-d H:i:s"),
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
                'type' => 'ideeprojet',
                'version' => '1.0',
            ],
        ]);
    }
}
