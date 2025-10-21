<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class TypeInterventionResource extends BaseApiResource
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
            "type_intervention"=> $this->type_intervention,
            "secteur"=> $this->when($this->secteur, function() {
                return [
                    "id" => $this->secteur->hashed_id,
                    "nom"=> $this->secteur->nom,
                    "type"=> $this->secteur->type
                ];
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
                'type' => 'typeintervention',
                'version' => '1.0',
            ],
        ]);
    }
}
