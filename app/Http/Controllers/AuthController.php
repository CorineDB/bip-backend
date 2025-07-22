<?php

namespace App\Http\Controllers;

use App\Http\Resources\AuthResource;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get Keycloak login URL
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $redirectUri = $request->get('redirect_uri', config('keycloak.redirect_uri'));
            $loginUrl = $this->authService->getLoginUrl($redirectUri);

            return response()->json([
                'success' => true,
                'login_url' => $loginUrl,
                'message' => 'URL de connexion Keycloak générée'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la génération de l\'URL de connexion: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la génération de l\'URL de connexion'
            ], 500);
        }
    }

    /**
     * Handle Keycloak callback and exchange code for token
     */
    public function callback(Request $request): JsonResponse
    {
        try {
            // Handle both GET (from Keycloak redirect) and POST (from frontend)
            $code = $request->input('code') ?? $request->query('code');
            $state = $request->input('state') ?? $request->query('state');

            if (!$code) {
                return response()->json([
                    'success' => false,
                    'message' => 'Code d\'autorisation manquant'
                ], 400);
            }

            $redirectUri = $request->get('redirect_uri', config('keycloak.redirect_uri'));
            $tokenData = $this->authService->exchangeCodeForToken($request->code, $redirectUri);

            if (!$tokenData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Échec de l\'échange du code d\'autorisation'
                ], 400);
            }

            // Get user info from token
            $userInfo = $this->authService->validateToken($tokenData['access_token']);

            if (!$userInfo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token invalide'
                ], 400);
            }

            // Get or create local user
            $user = $this->authService->getOrCreateUser($userInfo);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de créer ou récupérer l\'utilisateur'
                ], 500);
            }

            return response()->json([
                'success' => true,
                'data' => new AuthResource($tokenData),
                'message' => 'Connexion réussie'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données de requête invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors du callback Keycloak: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la connexion'
            ], 500);
        }
    }

    /**
     * Get current user profile
     */
    public function profile(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $keycloakUserInfo = $request->get('keycloak_user_info');

            return response()->json([
                'success' => true,
                'data' => new UserResource($user),
                'message' => 'Profil utilisateur récupéré'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du profil: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du profil'
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'settings' => 'sometimes|array'
            ]);

            $user = $request->user();
            $updateData = $request->only(['name', 'settings']);

            if (!empty($updateData)) {
                $user->update($updateData);
            }

            return response()->json([
                'success' => true,
                'user' => $user->fresh(),
                'message' => 'Profil mis à jour avec succès'
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données de requête invalides',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du profil: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du profil'
            ], 500);
        }
    }

    /**
     * Refresh access token
     */
    public function refresh(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'refresh_token' => 'required|string'
            ]);

            $tokenData = $this->authService->refreshToken($request->refresh_token);

            if (!$tokenData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token de rafraîchissement invalide'
                ], 400);
            }

            return AuthResource::refresh($tokenData)->response();
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token de rafraîchissement requis',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur lors du rafraîchissement du token: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du rafraîchissement du token'
            ], 500);
        }
    }

    /**
     * Logout user from Keycloak
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $token = $request->get('keycloak_token');

            if ($token) {
                $this->authService->logout($token);
            }

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion: ' . $e->getMessage());

            return response()->json([
                'success' => true,
                'message' => 'Déconnexion réussie'
            ]);
        }
    }

    /**
     * Get token introspection information
     */
    public function introspect(Request $request): JsonResponse
    {
        try {
            $token = $request->get('keycloak_token');

            if (!$token) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token requis'
                ], 400);
            }

            $introspection = $this->authService->introspectToken($token);

            if (!$introspection) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token invalide ou inactif'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'token_info' => $introspection,
                'message' => 'Introspection du token réussie'
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'introspection du token: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'introspection du token'
            ], 500);
        }
    }
}