<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class NotationResource extends BaseApiResource
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
            'critere_id' => $this->when($this->critere_id, $this->critere_id) ,
            'categorie_critere_id' => $this->categorie_critere_id,
            'libelle' => $this->libelle,
            'valeur' => $this->valeur,
            'commentaire' => $this->commentaire
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
                'type' => 'notation',
                'version' => '1.0',
            ],
        ]);
    }
}