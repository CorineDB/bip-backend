<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ProgrammeResource extends BaseApiResource
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
            "slug"=> $this->slug,
            "type_programme"=> $this->type_programme
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
                'type' => 'typeprogramme',
                'version' => '1.0',
            ],
        ]);
    }
}