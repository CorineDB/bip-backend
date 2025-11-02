<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class FicheIdeeResource extends BaseApiResource
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
            'id'          => $this->hashed_id,
            'nom'         => $this->nom,
            'description' => $this->description,
            'type'        => $this->type,
            'categorie'   => new CategorieDocumentResource($this->categorie),
            'metadata'    => $this->metadata,
            'structure'   => $this->structure,
            // Champs globaux (hors sections) - triés par ordre d'affichage
            'champs'    => $this->when($this->champs->count(), function() {
                return ChampResource::collection(
                    $this->champs->sortBy('ordre_affichage')
                );
            }),
            // Sections triées par ordre d'affichage, sections parents seulement
            'sections'    => $this->when($this->sections->count(), function() {
                return FicheIdeeSectionResource::collection(
                    $this->sections
                        ->whereNull('parentSectionId')
                        ->sortBy('ordre_affichage')
                );
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
                'type' => 'document',
                'version' => '1.0',
            ],
        ]);
    }
}
