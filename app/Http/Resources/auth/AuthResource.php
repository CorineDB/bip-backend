<?php

namespace App\Http\Resources\auth;

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
            "nom"=> $this->personne->nom,
            "prenom"=> $this->personne->prenom,
            "email"=> $this->email,
            "poste"=> $this->personne->poste,
            //"organisation"=> new OrganisationResource($this->organisation),
            "status"=> "actif",
            "role" => new RoleResource($this->role)

            /*"id" => $this->secure_id,
            "nom" => $this->nom,
            "email" => $this->email,
            "contact" => $this->contact,
            "type" => $this->type,
            "profil" => $this->when($this->type != 'administrateur', function(){
                return $this->profilable;
            }),
            "programme" => $this->when($this->type !== 'administrateur', $this->programme),
            "role" => RoleResource::collection($this->roles->load('permissions')),*/
        ];
    }
}
