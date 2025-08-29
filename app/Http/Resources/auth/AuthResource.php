<?php

namespace App\Http\Resources\auth;

use App\Http\Resources\GroupeUtilisateurResource;
use App\Http\Resources\OrganisationResource;
use App\Http\Resources\RoleResource;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            "id" => $this->id,
            "type" => $this->type,
            "nom"=> $this->personne->nom,
            "prenom"=> $this->personne->prenom,
            "email"=> $this->email,
            "poste"=> $this->personne->poste,
            "organisation"=> $this->whenLoaded('organisation', function(){
                return new OrganisationResource($this->organisation);
            }),
            "status"=> $this->status,
            "role" => new RoleResource($this->role->load("permissions")),
            "groupes_utilisateur" => new GroupeUtilisateurResource($this->groupesUtilisateur)
        ];
    }
}
