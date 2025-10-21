<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class DpafResource extends BaseApiResource
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
            "nom" => $this->nom,
            "description" => $this->description,
            "admin" => $this->when($this->user, function () {
                return new UserResource($this->user);
            }),
            "ministere" => $this->when($this->ministere, function () {
                return [
                    "id" => $this->ministere->hashed_id,
                    "nom" => $this->ministere->nom,
                    "type" => $this->ministere->type
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
                'type' => 'dpaf',
                'version' => '1.0',
            ],
        ]);
    }
}
