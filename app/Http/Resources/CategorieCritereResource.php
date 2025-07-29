<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;

class CategorieCritereResource extends BaseApiResource
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
            'type' => $this->type,
            'slug' => $this->slug,
            'is_mandatory' => $this->is_mandatory,
            'criteres' => CritereResource::collection($this->criteres),
            'notations' => NotationResource::collection($this->whenLoaded('notations')),
            'total_ponderation' => $this->whenLoaded('criteres', function () {
                return $this->criteres->sum('ponderation');
            }),
            'updated_at' => Carbon::parse($this->updated_at)->format("d/m/y H:i:s")
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
                'type' => 'categoriecritere',
                'version' => '1.0',
            ],
        ]);
    }
}