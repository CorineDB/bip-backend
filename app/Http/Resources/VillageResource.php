<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class VillageResource extends BaseApiResource
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
            'slug' => $this->slug,
            'nom' => $this->nom,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'arrondissementId' => $this->arrondissement?->hashed_id
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
                'type' => 'village',
                'version' => '1.0',
            ],
        ]);
    }
}
