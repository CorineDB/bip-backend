<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'access_token' => $this->resource['access_token'] ?? null,
            'refresh_token' => $this->resource['refresh_token'] ?? null,
            'expires_in' => $this->resource['expires_in'] ?? null,
            'token_type' => $this->resource['token_type'] ?? 'Bearer',
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
        return [
            'success' => true,
            //'message' => 'Authentification réussie',
            'timestamp' => now()->toISOString(),
        ];
    }

    /**
     * Customize the response for a request.
     *
     * @param Request $request
     * @param \Illuminate\Http\JsonResponse $response
     * @return void
     */
    public function withResponse(Request $request, $response): void
    {
        $response->header('Content-Type', 'application/json');
    }

    /**
     * Create response for successful token refresh
     *
     * @param array $tokenData
     * @return static
     */
    public static function refresh(array $tokenData): static
    {
        return (new static($tokenData))->additional([
            'message' => 'Token rafraîchi avec succès',
        ]);
    }

    /**
     * Create response for successful login
     *
     * @param array $tokenData
     * @param array|null $userInfo
     * @return static
     */
    public static function login(array $tokenData, ?array $userInfo = null): static
    {
        $resource = new static($tokenData);

        if ($userInfo) {
            $resource = $resource->additional([
                'user' => [
                    'id' => $userInfo['id'] ?? null,
                    'email' => $userInfo['email'] ?? null,
                    'name' => $userInfo['name'] ?? null,
                ],
            ]);
        }

        return $resource->additional([
            'message' => 'Connexion réussie',
        ]);
    }
}