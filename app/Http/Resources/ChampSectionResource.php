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
            'id'               => $this->id,
            'intitule'         => $this->intitule,
            'ordre_affichage'  => $this->ordre_affichage,
            'type'             => $this->type,
            'champs'           => ChampResource::collection($this->champs)
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
                'type' => 'champ',
                'version' => '1.0',
            ],
        ]);
    }
}