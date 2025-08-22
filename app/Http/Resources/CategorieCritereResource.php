<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;

class CategorieCritereResource extends BaseApiResource
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
            'type' => $this->type,
            'slug' => $this->slug,
            'is_mandatory' => $this->is_mandatory,
            'criteres' => CritereResource::collection($this->criteres),
            'notations' => $this->when($this->notations, function() {
                return NotationResource::collection($this->notations);
            }),
            'total_ponderation' => $this->whenLoaded('criteres', function () {
                return $this->criteres->sum('ponderation');
            }),
            'fichiers' => $this->when($this->relationLoaded('fichiers'), function() {
                return $this->fichiers->map(function ($fichier) {
                    return [
                        'id' => $fichier->id,
                        'nom_original' => $fichier->nom_original,
                        'extension' => $fichier->extension,
                        'mime_type' => $fichier->mime_type,
                        'taille_formatee' => $fichier->taille_formatee,
                        'url' => $fichier->url,
                        'categorie' => $fichier->categorie,
                        'description' => $fichier->description,
                        'commentaire' => $fichier->commentaire,
                        'is_public' => $fichier->is_public,
                        'is_active' => $fichier->is_active,
                        'created_at' => $fichier->created_at->format('Y-m-d H:i:s')
                    ];
                });
            }),
            'updated_at' => Carbon::parse($this->updated_at)->format("d/m/y H:i:s")
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
                'type' => 'categoriecritere',
                'version' => '1.0',
            ],
        ]);
    }
}