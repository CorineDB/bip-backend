<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class LieuInterventionResource extends BaseApiResource
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
            "id" => $this->id,
            "village"=> $this->village ? new VillageResource($this->village) : null,
            "arrondissement"=> $this->arrondissement ? new ArrondissementResource($this->arrondissement) : null,
            "commune"=> $this->commune ? new CommuneResource($this->commune) : null,
            "departement"=> $this->departement ? new DepartementResource($this->departement) : null,
            "projetable_id"=> $this->projetable_id,
            "projetable_type"=> $this->projetable_type
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
                'type' => 'lieu_intervention',
                'version' => '1.0',
            ],
        ]);
    }
}