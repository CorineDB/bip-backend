<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DocumentResource extends BaseApiResource
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
            'id'          => $this->id,
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
                return ChampSectionResource::collection(
                    $this->sections
                        ->whereNull('parentSectionId')
                        ->sortBy('ordre_affichage')
                );
            }),
            'forms'       => $this->buildOrderedElementsResource()
        ];
    }

    /**
     * Construire la liste ordonnée des éléments transformés en ressources
     */
    private function buildOrderedElementsResource()
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
                'type' => 'document',
                'version' => '1.0',
            ],
        ]);
    }
}