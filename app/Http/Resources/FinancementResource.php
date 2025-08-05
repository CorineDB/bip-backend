<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class FinancementResource extends BaseApiResource
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
            "nom" => $this->nom,
            "nom_usuel" => $this->nom_usuel,
            "type" => $this->type,
            "financement" => $this->when($this->parent, function () {
                return [
                    "id" => $this->parent->id,
                    "nom" => $this->parent->nom,
                    "nom_usuel" => $this->parent->nom_usuel,
                    "type" => $this->parent->type,
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
                'type' => 'financement',
                'version' => '1.0',
            ],
        ]);
    }
}
