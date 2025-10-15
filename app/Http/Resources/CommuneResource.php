<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CommuneResource extends BaseApiResource
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
            'id' => $this->hashed_id,
            'slug' => $this->slug,
            'nom' => $this->nom,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'departementId' => $this->departementId,
            'departementId' => $this->departement?->hashed_id
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
                'type' => 'commune',
                'version' => '1.0',
            ],
        ]);
    }
}
