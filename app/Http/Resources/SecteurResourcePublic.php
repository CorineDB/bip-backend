<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class SecteurResourcePublic extends BaseApiResource
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
            "id" => $this->hashed_id,
            "nom"=> $this->nom,
            "type"=> $this->type
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
                'type' => 'secteur',
                'version' => '1.0',
            ],
        ]);
    }
}
