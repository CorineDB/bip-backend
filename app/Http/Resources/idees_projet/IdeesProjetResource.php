<?php

namespace App\Http\Resources\idees_projet;

use App\Http\Resources\BaseApiResource;
use App\Http\Resources\CibleResource;
use App\Http\Resources\FinancementResource;
use App\Http\Resources\LieuInterventionResource;
use App\Http\Resources\OddResource;
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
            'id' => $this->id,
            // Identifiants et métadonnées
            'identifiant_bip' => $this->identifiant_bip,
            'identifiant_sigfp' => $this->identifiant_sigfp,
            'sigle' => $this->sigle,
            'titre_projet' => $this->titre_projet,

            // Statuts et phases
            'statut' => $this->statut?->value ?? $this->statut,
            'est_coherent' => $this->est_coherent,
            'est_soumise' => $this->est_soumise,

            // Descriptions et contenus principaux
            'description_projet' => $this->description_projet,

            // Détails techniques et organisationnels
            'duree' => $this->duree,
            // Données JSON structurées
            'cout_estimatif_projet' => $this->cout_estimatif_projet ?? []
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
