<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DetailsSecteurResource extends BaseApiResource
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
            "type"=> $this->type,
            "secteurs"=> $this->when($this->type->value == "grand-secteur", function(){
                return DetailsSecteurResource::collection($this->children->where("type", "secteur"));
            }),
            "sous_secteurs"=> $this->when($this->type->value == "secteur", function(){
                return DetailsSecteurResource::collection($this->children->where("type", "sous-secteur"));
            })
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
