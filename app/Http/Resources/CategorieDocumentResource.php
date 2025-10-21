<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class CategorieDocumentResource extends BaseApiResource
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
            "slug"=> $this->slug,
            "description"=> $this->description,
            "format"=> $this->format
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
                'type' => 'categoriedocument',
                'version' => '1.0',
            ],
        ]);
    }
}
