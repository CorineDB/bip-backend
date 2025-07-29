<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CritereResource extends BaseApiResource
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
            'categorie_critere_id' => $this->categorie_critere_id,
            'intitule' => $this->intitule,
            'ponderation' => $this->ponderation,
            'commentaire' => $this->commentaire,
            'is_mandatory' => $this->is_mandatory,
            'notations' => NotationResource::collection($this->whenLoaded('notations'))
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
                'type' => 'critere',
                'version' => '1.0',
            ],
        ]);
    }
}