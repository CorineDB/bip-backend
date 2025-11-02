<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class FicheIdeeSectionResource extends BaseApiResource
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
            'id'               => $this->hashed_id,
            'key'              => $this->slug,
            'intitule'         => $this->intitule,
            'description'      => $this->description,
            'ordre_affichage'  => $this->ordre_affichage,
            'type'             => $this->type,
            'parentSectionId'  => $this->parentSection?->hashed_id,
            // Champs triés par ordre d'affichage
            'champs'    => $this->when($this->champs->count(), function() {
                return ChampResource::collection(
                    $this->champs->sortBy('ordre_affichage')
                );
            }),
            // Sous-sections (enfants) triées par ordre d'affichage
            'sous_sections'    => $this->when($this->childSections->count(), function() {
                return FicheIdeeSectionResource::collection(
                    $this->childSections->sortBy('ordre_affichage')
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
                'type' => 'section',
                'version' => '1.0',
            ],
        ]);
    }
}
