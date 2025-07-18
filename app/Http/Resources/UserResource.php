<?php

namespace App\Http\Resources;

use App\Models\Organisation;
use Illuminate\Http\Request;

class UserResource extends BaseApiResource
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
            "nom"=> $this->personne->nom,
            "prenom"=> $this->personne->prenom,
            "email"=> $this->email,
            "poste"=> $this->personne->poste,
            "organisation"=> new OrganisationResource($this->organisation),
            "status"=> "actif",
            "role" => new RoleResource($this->role)
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