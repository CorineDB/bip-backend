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
            // Champs globaux (hors sections)
            'champs'      => $this->whenLoaded("champs", ChampResource::collection($this->champs)),
            'sections'      => $this->when("sections", ChampSectionResource::collection($this->sections)),
        ];
        return parent::toArray($request);
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