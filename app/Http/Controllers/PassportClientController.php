<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePassportClientRequest;
use App\Http\Requests\UpdatePassportClientRequest;
use App\Http\Resources\PassportClientResource;
use App\Services\PassportOAuthService;
use App\Services\Traits\ResponseJsonTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class PassportClientController extends Controller
{
    use ResponseJsonTrait;

    protected PassportOAuthService $passportService;

    public function __construct(PassportOAuthService $passportService)
    {
        $this->passportService = $passportService;
    }

    /**
     * Liste tous les clients Passport
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['personal_access_client', 'password_client', 'revoked', 'name']);
        $perPage = $request->get('per_page', 15);

        return $this->passportService->getClients($filters, $perPage);
    }

    /**
     * Affiche un client spécifique
     */
    public function show(string $id): JsonResponse
    {
        return $this->passportService->getClient($id);
    }

    /**
     * Crée un nouveau client Passport
     */
    public function store(StorePassportClientRequest $request): JsonResponse
    {
        return $this->passportService->createClient($request->validated());
    }

    /**
     * Crée un client credentials (client_credentials grant)
     */
    public function storeClientCredentials(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:oauth_clients,name',
            'redirect_uris' => 'required|array',
            'redirect_uris.*' => 'distinct|string|max:255',
        ]);

        return $this->passportService->createClientCredentials($validated);
    }

    /**
     * Crée un client d'accès personnel
     */
    public function storePersonalAccessClient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:oauth_clients,name',
        ]);

        return $this->passportService->createPersonalAccessClient($validated);
    }

    /**
     * Crée un client password grant
     */
    public function storePasswordClient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:oauth_clients,name',
        ]);

        return $this->passportService->createPasswordClient($validated);
    }

    /**
     * Crée un client authorization code
     */
    public function storeAuthorizationCodeClient(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:oauth_clients,name',
            'redirect' => 'required|string|url|max:500',
            'confidential' => 'boolean',
        ]);

        return $this->passportService->createAuthorizationCodeClient($validated);
    }

    /**
     * Liste tous les clients credentials
     */
    public function indexClientCredentials(Request $request): JsonResponse
    {
        $filters = $request->only(['revoked', 'name']);
        $perPage = $request->get('per_page', 15);

        return $this->passportService->getClientCredentials($filters, $perPage);
    }

    /**
     * Liste tous les clients d'accès personnel
     */
    public function indexPersonalAccessClients(Request $request): JsonResponse
    {
        $filters = $request->only(['revoked', 'name']);
        $perPage = $request->get('per_page', 15);

        return $this->passportService->getPersonalAccessClients($filters, $perPage);
    }

    /**
     * Liste tous les clients password grant
     */
    public function indexPasswordClients(Request $request): JsonResponse
    {
        $filters = $request->only(['revoked', 'name']);
        $perPage = $request->get('per_page', 15);

        return $this->passportService->getPasswordClients($filters, $perPage);
    }

    /**
     * Liste tous les clients authorization code
     */
    public function indexAuthorizationCodeClients(Request $request): JsonResponse
    {
        $filters = $request->only(['revoked', 'name']);
        $perPage = $request->get('per_page', 15);

        return $this->passportService->getAuthorizationCodeClients($filters, $perPage);
    }

    /**
     * Retrieve a client credentials
     */
    public function findClientCredentials(string $id): JsonResponse
    {
        return $this->passportService->getClient($id);
    }

    /**
     * Retrieve a client d'accès personnel
     */
    public function findPersonalAccessClients(string $id): JsonResponse
    {
        return $this->passportService->getClient($id);
    }

    /**
     * Retrieve a client password grant
     */
    public function findPasswordClients(string $id): JsonResponse
    {
        return $this->passportService->getClient($id);
    }

    /**
     * Retrieve a client authorization code
     */
    public function findAuthorizationCodeClients(string $id): JsonResponse
    {
        return $this->passportService->getClient($id);
    }

    /**
     * Met à jour un client credentials
     */
    public function updateClientCredentials(UpdatePassportClientRequest $request, string $id): JsonResponse
    {
        return $this->passportService->updateClientCredentials($id, $request->validated());
    }

    /**
     * Met à jour un client d'accès personnel
     */
    public function updatePersonalAccessClient(UpdatePassportClientRequest $request, string $id): JsonResponse
    {
        return $this->passportService->updatePersonalAccessClient($id, $request->validated());
    }

    /**
     * Met à jour un client password grant
     */
    public function updatePasswordClient(UpdatePassportClientRequest $request, string $id): JsonResponse
    {
        return $this->passportService->updatePasswordClient($id, $request->validated());
    }

    /**
     * Met à jour un client authorization code
     */
    public function updateAuthorizationCodeClient(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('oauth_clients', 'name')->ignore($id)
            ],
            'redirect' => 'sometimes|required|string|url|max:500',
            'confidential' => 'boolean',
        ]);

        return $this->passportService->updateAuthorizationCodeClient($id, $validated);
    }

    /**
     * Met à jour un client existant
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'redirect' => 'nullable|string|url',
        ]);

        return $this->passportService->updateClient($id, $validated);
    }

    /**
     * Révoque un client (soft delete)
     */
    public function destroy(string $id): JsonResponse
    {
        return $this->passportService->revokeClient($id);
    }

    /**
     * Régénère le secret d'un client
     */
    public function regenerateSecret(string $id): JsonResponse
    {
        return $this->passportService->regenerateClientSecret($id);
    }

    /**
     * Restaure un client révoqué
     */
    public function restore(string $id): JsonResponse
    {
        return $this->passportService->restoreClient($id);
    }

    /**
     * Supprime définitivement un client
     */
    public function forceDelete(string $id): JsonResponse
    {
        return $this->passportService->deleteClient($id);
    }

    /**
     * Liste les scopes disponibles
     */
    public function availableScopes(): JsonResponse
    {
        return $this->passportService->getAvailableScopes();
    }

    /**
     * Récupère les statistiques des clients
     */
    public function stats(): JsonResponse
    {
        return $this->passportService->getClientStats();
    }

    /**
     * Recherche des clients
     */
    public function search(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => 'required|string|min:1',
            'per_page' => 'integer|min:1|max:100'
        ]);

        $search = $validated['q'];
        $perPage = $validated['per_page'] ?? 15;

        return $this->passportService->searchClients($search, $perPage);
    }

    /**
     * Récupère les tokens actifs d'un client
     */
    public function activeTokens(string $id): JsonResponse
    {
        return $this->passportService->getClientActiveTokens($id);
    }

    /**
     * Révoque tous les tokens d'un client
     */
    public function revokeTokens(string $id): JsonResponse
    {
        return $this->passportService->revokeClientTokens($id);
    }

    // =============================================================================
    // GESTION DE LA SÉCURITÉ
    // =============================================================================

    /**
     * Rotation automatique des secrets expirés
     */
    public function rotateExpiredSecrets(Request $request): JsonResponse
    {
        $daysOld = $request->get('days_old', 90);
        return $this->passportService->rotateExpiredSecrets($daysOld);
    }

    /**
     * Force la rotation du secret d'un client
     */
    public function forceRotateSecret(string $id, Request $request): JsonResponse
    {
        $reason = $request->get('reason', 'Manual rotation via API');
        return $this->passportService->forceRotateClientSecret($id, $reason);
    }

    /**
     * Nettoie les tokens expirés
     */
    public function cleanupTokens(): JsonResponse
    {
        return $this->passportService->cleanupExpiredTokens();
    }

    /**
     * Audit des accès OAuth
     */
    public function auditAccess(Request $request): JsonResponse
    {
        $filters = $request->only([
            'client_id', 'user_id', 'revoked',
            'date_from', 'date_to', 'per_page'
        ]);

        return $this->passportService->auditOAuthAccess($filters);
    }

    // =============================================================================
    // GESTION DE L'EXPIRATION ET DU RAFRAÎCHISSEMENT DES TOKENS
    // =============================================================================

    /**
     * Vérifie et révoque les tokens expirés
     */
    public function checkExpiredTokens(): JsonResponse
    {
        return $this->passportService->checkExpiredTokens();
    }

    /**
     * Rafraîchit un token d'accès
     */
    public function refreshToken(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'refresh_token' => 'required|string'
        ]);

        return $this->passportService->refreshAccessToken($validated['refresh_token']);
    }

    /**
     * Récupère les informations d'expiration d'un token
     */
    public function getTokenExpiration(string $tokenId): JsonResponse
    {
        return $this->passportService->getTokenExpirationInfo($tokenId);
    }

    /**
     * Configure les durées d'expiration des tokens
     */
    public function configureExpiration(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'access_token_lifetime_hours' => 'sometimes|integer|min:1|max:168',
            'refresh_token_lifetime_hours' => 'sometimes|integer|min:1|max:720',
            'personal_access_token_lifetime_days' => 'sometimes|integer|min:1|max:365'
        ]);

        return $this->passportService->configureTokenExpiration($validated);
    }

    /**
     * Récupère les statistiques d'expiration des tokens
     */
    public function expirationStats(): JsonResponse
    {
        return $this->passportService->getTokenExpirationStats();
    }
}