<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class KeycloakAuthService
{
    private string $keycloakUrl;
    private string $realm;
    private string $clientId;
    private string $clientSecret;

    public function __construct()
    {
        $this->keycloakUrl = config('keycloak.server_url');
        $this->realm = config('keycloak.realm');
        $this->clientId = config('keycloak.client_id');
        $this->clientSecret = config('keycloak.client_secret');
    }

    /**
     * Validate Keycloak access token
     */
    public function validateToken(string $token): ?array
    {
        try {
            $cacheKey = 'keycloak_token_' . md5($token);

            // Check cache first
            if (Cache::has($cacheKey)) {
                return Cache::get($cacheKey);
            }

            $url = $this->keycloakUrl . '/realms/' . $this->realm . '/protocol/openid-connect/userinfo';

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->get($url);

            Log::info('Keycloak token validation attempt', [
                'url' => $url,
                'status' => $response->status(),
                'body' => substr($response->body(), 0, 500), // First 500 chars
                'token_preview' => substr($token, 0, 20) . '...' // First 20 chars
            ]);

            if ($response->successful()) {
                $userInfo = $response->json();

                // Cache for 5 minutes
                Cache::put($cacheKey, $userInfo, 300);

                return $userInfo;
            }

            Log::warning('Keycloak token validation failed', [
                'status' => $response->status(),
                'error' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Keycloak token validation failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get or create user from Keycloak token
     */
    public function getOrCreateUser(array $keycloakUserInfo): ?User
    {
        try {
            $email = $keycloakUserInfo['email'] ?? null;
            $sub = $keycloakUserInfo['sub'] ?? null;

            if (!$email || !$sub) {
                return null;
            }

            // Find user by Keycloak sub or email
            $user = User::where('keycloak_id', $sub)
                       ->orWhere('email', $email)
                       ->first();

            if (!$user) {
                // User must exist in local database first
                Log::warning('User not found in local database', [
                    'keycloak_id' => $sub,
                    'email' => $email,
                    'preferred_username' => $keycloakUserInfo['preferred_username'] ?? 'N/A'
                ]);
                return null;
            } else {
                // Update existing user with Keycloak ID if not set
                if (!$user->keycloak_id) {
                    $user->update(['keycloak_id' => $sub]);
                }
            }

            return $user;
        } catch (\Exception $e) {
            Log::error('Failed to get or create user from Keycloak: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user profile from Keycloak
     */
    public function getUserProfile(string $token): ?array
    {
        return $this->validateToken($token);
    }

    /**
     * Logout user from Keycloak
     */
    public function logout(string $token): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $token,
            ])->post($this->keycloakUrl . '/realms/' . $this->realm . '/protocol/openid-connect/logout');

            // Clear token from cache
            $cacheKey = 'keycloak_token_' . md5($token);
            Cache::forget($cacheKey);


            /* Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Optionnel : déconnexion de Keycloak
            $keycloakLogoutUrl = config('services.keycloak.base_url') .
                                '/realms/' . config('services.keycloak.realms') .
                                '/protocol/openid-connect/logout';

            return redirect($keycloakLogoutUrl); */

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Keycloak logout failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Keycloak login URL
     */
    public function getLoginUrl(string $redirectUri): string
    {
        $params = [
            'client_id' => $this->clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'state' => csrf_token(),
            'prompt' => 'login', // Force new login
        ];

        return $this->keycloakUrl . '/realms/' . $this->realm . '/protocol/openid-connect/auth?' . http_build_query($params);
    }

    /**
     * Exchange authorization code for access token
     */
    public function exchangeCodeForToken(string $code, string $redirectUri): ?array
    {
        try {
            $response = Http::asForm()->post($this->keycloakUrl . '/realms/' . $this->realm . '/protocol/openid-connect/token', [
                'grant_type' => 'authorization_code',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'code' => $code,
                'redirect_uri' => $redirectUri,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Keycloak token exchange failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(string $refreshToken): ?array
    {
        try {
            $response = Http::asForm()->post($this->keycloakUrl . '/realms/' . $this->realm . '/protocol/openid-connect/token', [
                'grant_type' => 'refresh_token',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $refreshToken,
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Keycloak token refresh failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get Keycloak user by email
     */
    public function getKeycloakUserByEmail(string $email): ?array
    {
        try {
            // Get admin token first
            $adminToken = $this->getAdminToken();
            if (!$adminToken) {
                return null;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $adminToken,
            ])->get($this->keycloakUrl . '/admin/realms/' . $this->realm . '/users', [
                'email' => $email,
                'exact' => true
            ]);

            if ($response->successful()) {
                $users = $response->json();
                return !empty($users) ? $users[0] : null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get Keycloak user by email: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create user in Keycloak
     */
    public function createKeycloakUser(array $userData): ?string
    {
        try {
            // Get admin token first
            $adminToken = $this->getAdminToken();
            if (!$adminToken) {
                Log::error('Failed to get admin token for creating Keycloak user');
                return null;
            }

            $temporaryPassword = $userData['password'] ?? 'ChangeMe123!';

            $keycloakUser = [
                'username' => $userData['username'] ?? $userData['email'],
                'email' => $userData['email'],
                'firstName' => $userData['first_name'] ?? '',
                'lastName' => $userData['last_name'] ?? '',
                'enabled' => true,
                'emailVerified' => true,
                'credentials' => [
                    [
                        'type' => 'password',
                        'value' => $temporaryPassword,
                        'temporary' => true // Force password reset on first login
                    ]
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $adminToken,
                'Content-Type' => 'application/json'
            ])->post($this->keycloakUrl . '/admin/realms/' . $this->realm . '/users', $keycloakUser);

            if ($response->status() === 201) {
                // Get the created user ID from Location header
                $location = $response->header('Location');
                if ($location) {
                    $keycloakId = basename($location);
                    Log::info('Keycloak user created successfully', ['keycloak_id' => $keycloakId, 'email' => $userData['email']]);
                    return $keycloakId;
                }

                // Fallback: find user by email
                sleep(1); // Wait a moment for user to be created
                $createdUser = $this->getKeycloakUserByEmail($userData['email']);
                return $createdUser['id'] ?? null;
            }

            Log::error('Failed to create Keycloak user', [
                'status' => $response->status(),
                'body' => $response->body(),
                'email' => $userData['email']
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Error creating Keycloak user: ' . $e->getMessage(), ['email' => $userData['email']]);
            return null;
        }
    }

    /**
     * Get admin access token for Keycloak admin API
     */
    private function getAdminToken(): ?string
    {
        try {
            $response = Http::asForm()->post($this->keycloakUrl . '/realms/' . $this->realm . '/protocol/openid-connect/token', [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['access_token'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get admin token: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Introspect token to get detailed information
     */
    public function introspectToken(string $token): ?array
    {
        try {
            $response = Http::asForm()->post($this->keycloakUrl . '/realms/' . $this->realm . '/protocol/openid-connect/token/introspect', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'token' => $token,
            ]);

            if ($response->successful()) {
                $result = $response->json();
                return $result['active'] ? $result : null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Keycloak token introspection failed: ' . $e->getMessage());
            return null;
        }
    }
}