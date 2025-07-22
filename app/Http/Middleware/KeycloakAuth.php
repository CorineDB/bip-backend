<?php

namespace App\Http\Middleware;

use App\Services\AuthService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class KeycloakAuth
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Extract token from Authorization header
            $token = $this->extractToken($request);

            if (!$token) {
                return $this->unauthorizedResponse('Token manquant');
            }

            // Validate token with Keycloak
            $userInfo = $this->authService->validateToken($token);

            if (!$userInfo) {
                return $this->unauthorizedResponse('Token invalide ou expiré');
            }

            // Get or find local user (don't create automatically)
            $user = $this->authService->getOrCreateUser($userInfo);

            if (!$user) {
                Log::warning('User not found in local database during authentication', [
                    'keycloak_id' => $userInfo['sub'] ?? 'unknown',
                    'email' => $userInfo['email'] ?? 'unknown'
                ]);
                return $this->unauthorizedResponse('Utilisateur non trouvé. Contactez l\'administrateur.');
            }

            // Set authenticated user for Laravel
            Auth::setUser($user);

            // Add Keycloak data to request for controllers
            $request->merge([
                'keycloak_user_info' => $userInfo,
                'keycloak_token' => $token,
                'authenticated_user' => $user
            ]);

            // Update last connection
            $user->update([
                'last_connection' => now(),
                'ip_address' => $request->ip()
            ]);

            return $next($request);

        } catch (\Exception $e) {
            Log::error('Keycloak authentication error: ' . $e->getMessage(), [
                'url' => $request->url(),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);

            return $this->unauthorizedResponse('Erreur d\'authentification');
        }
    }

    /**
     * Extract token from Authorization header
     */
    private function extractToken(Request $request): ?string
    {
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $request->bearerToken(); // Fallback to Laravel's method
        }

        return substr($authHeader, 7);
    }

    /**
     * Return consistent unauthorized response
     */
    private function unauthorizedResponse(string $message): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => 'unauthorized'
        ], 401);
    }
}