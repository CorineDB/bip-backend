<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PassportClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'secret' => $this->when(
                !$this->revoked && $this->secret,
                $this->secret
            ),
            'provider' => $this->provider,
            'redirect_uris' => $this->redirect_uris,
            'grant_types' => $this->grant_types[0],
            'revoked' => (bool) $this->revoked,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'is_confidential' => $this->isConfidential(),
            'is_personal_access_client' => $this->isPersonalAccessClient(),
            'is_password_client' => $this->isPasswordClient(),
            'client_type' => $this->getClientType(),
            'status' => $this->getStatus(),
            'active_tokens_count' => $this->whenLoaded('tokens', function () {
                return $this->tokens()
                    ->where('revoked', false)
                    ->where('expires_at', '>', now())
                    ->count();
            }),
            'total_tokens_count' => $this->whenLoaded('tokens', function () {
                return $this->tokens()->count();
            }),
            'created_at' => Carbon::parse($this->created_at?->toISOString())->format("Y-m-d H:i:s"),
            'updated_at' => Carbon::parse($this->updated_at?->toISOString())->format("Y-m-d H:i:s"),
        ];
    }

    /**
     * Vérifie si le client est confidentiel
     */
    private function isConfidential(): bool
    {
        return !empty($this->secret);
    }

    /**
     * Vérifie si c'est un client d'accès personnel
     */
    private function isPersonalAccessClient(): bool
    {
        $grantTypes = $this->grant_types ?: [];
        return in_array('personal_access', $grantTypes) ||
               in_array('personal_access_token', $grantTypes);
    }

    /**
     * Vérifie si c'est un client password grant
     */
    private function isPasswordClient(): bool
    {
        $grantTypes = $this->grant_types ?: [];
        return in_array('password', $grantTypes);
    }

    /**
     * Détermine le type principal du client
     */
    private function getClientType(): string
    {
        if ($this->isPersonalAccessClient()) {
            return 'personal_access';
        }

        if ($this->isPasswordClient()) {
            return 'password_grant';
        }

        $grantTypes = $this->grant_types ?: [];

        if (in_array('client_credentials', $grantTypes)) {
            return 'client_credentials';
        }

        if (in_array('authorization_code', $grantTypes)) {
            return 'authorization_code';
        }

        return 'unknown';
    }

    /**
     * Détermine le statut du client
     */
    private function getStatus(): string
    {
        if ($this->revoked) {
            return 'revoked';
        }

        return 'active';
    }
}