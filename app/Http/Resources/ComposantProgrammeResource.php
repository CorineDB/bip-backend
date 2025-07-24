<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;

class ComposantProgrammeResource extends BaseApiResource
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
            "indice"=> $this->indice,
            "intitule"=> $this->intitule,
            "programme_ou_composant"=> $this->when($this->typeProgramme, function() {
                return [
                    "id" => $this->typeProgramme->id,
                    "type_programme"=> $this->typeProgramme->type_programme
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
                'type' => 'composantprogramme',
                'version' => '1.0',
            ],
        ]);
    }
}