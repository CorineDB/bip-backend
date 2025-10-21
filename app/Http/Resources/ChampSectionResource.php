<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ChampSectionResource extends BaseApiResource
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
                return ChampSectionResource::collection(
                    $this->childSections->sortBy('ordre_affichage')
                );
            }),
            // Éléments mélangés (champs et sous-sections) triés par ordre d'affichage
            'elements' => $this->buildOrderedSectionElements()
        ];
    }

    /**
     * Construire la liste ordonnée des éléments de la section
     */
    private function buildOrderedSectionElements()
    {
        return $this->getOrderedElements()->map(function ($element) {
            $baseData = [
                'element_type' => $element['element_type'],
                'ordre_affichage' => $element['ordre_affichage']
            ];

            if ($element['type'] === 'champ') {
                $champResource = new ChampResource($element['data']);
                return array_merge($baseData, $champResource->toArray(request()));
            } else {
                $sectionResource = new ChampSectionResource($element['data']);
                return array_merge($baseData, $sectionResource->toArray(request()));
            }
        })->values();
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
