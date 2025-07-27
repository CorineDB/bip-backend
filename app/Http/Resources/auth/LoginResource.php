<?php

namespace App\Http\Resources\auth;

use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
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
            "access_token" => $this['accessToken'],
            "expired_at" => $this['expiresIn'],
            "utilisateur" => $this->when(auth()->check(), function(){
                return new AuthResource(auth()->user());
            })
        ];
    }
}
