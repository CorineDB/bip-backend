<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Laravel\Passport\Token;
use Laravel\Passport\TokenRepository;
use Symfony\Component\HttpFoundation\Response;

class OAuthAuditMiddleware
{
    protected TokenRepository $tokenRepository;

    public function __construct(TokenRepository $tokenRepository)
    {
        $this->tokenRepository = $tokenRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Capturer les informations avant la requête
        $requestData = $this->captureRequestData($request);

        // Exécuter la requête
        $response = $next($request);

        // Capturer les informations après la requête
        $endTime = microtime(true);
        $responseData = $this->captureResponseData($response, $endTime - $startTime);

        // Logger les informations d'audit
        $this->logAuditData($requestData, $responseData);

        return $response;
    }

    /**
     * Capture les données de la requête
     */
    private function captureRequestData(Request $request): array
    {
        $user = Auth::user();
        $token = $request->user() ? $request->user()->token() : null;

        return [
            'timestamp' => now()->toISOString(),
            'request_id' => $request->header('X-Request-ID', uniqid()),
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'route' => $request->route() ? $request->route()->getName() : null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'client_id' => $token?->client_id,
            'token_id' => $token?->id,
            'scopes' => $token?->scopes ?? [],
            'headers' => $this->filterHeaders($request->headers->all()),
            'query_params' => $request->query(),
            'request_size' => strlen($request->getContent()),
        ];
    }

    /**
     * Capture les données de la réponse
     */
    private function captureResponseData(Response $response, float $duration): array
    {
        return [
            'status_code' => $response->getStatusCode(),
            'response_size' => strlen($response->getContent()),
            'duration_ms' => round($duration * 1000, 2),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
        ];
    }

    /**
     * Filtre les headers sensibles
     */
    private function filterHeaders(array $headers): array
    {
        $sensitiveHeaders = [
            'authorization',
            'x-api-key',
            'cookie',
            'set-cookie',
            'x-csrf-token',
        ];

        $filteredHeaders = [];
        foreach ($headers as $key => $value) {
            if (in_array(strtolower($key), $sensitiveHeaders)) {
                $filteredHeaders[$key] = '[REDACTED]';
            } else {
                $filteredHeaders[$key] = $value;
            }
        }

        return $filteredHeaders;
    }

    /**
     * Log les données d'audit
     */
    private function logAuditData(array $requestData, array $responseData): void
    {
        $auditData = array_merge($requestData, $responseData);

        // Déterminer le niveau de log selon le status code
        $logLevel = 'info';
        if ($responseData['status_code'] >= 400 && $responseData['status_code'] < 500) {
            $logLevel = 'warning';
        } elseif ($responseData['status_code'] >= 500) {
            $logLevel = 'error';
        }

        // Log principal
        Log::channel('oauth_audit')->$logLevel('OAuth API Access', $auditData);

        // Log spécial pour les erreurs d'authentification
        if ($responseData['status_code'] === 401) {
            Log::channel('oauth_audit')->warning('OAuth Authentication Failed', [
                'ip_address' => $requestData['ip_address'],
                'user_agent' => $requestData['user_agent'],
                'url' => $requestData['url'],
                'timestamp' => $requestData['timestamp'],
            ]);
        }

        // Log spécial pour les accès sensibles
        if ($this->isSensitiveEndpoint($requestData['route'])) {
            Log::channel('oauth_audit')->notice('Sensitive OAuth Endpoint Access', [
                'route' => $requestData['route'],
                'user_id' => $requestData['user_id'],
                'client_id' => $requestData['client_id'],
                'ip_address' => $requestData['ip_address'],
                'timestamp' => $requestData['timestamp'],
            ]);
        }

        // Alertes pour comportements suspects
        $this->checkSuspiciousActivity($auditData);
    }

    /**
     * Vérifie si l'endpoint est sensible
     */
    private function isSensitiveEndpoint(?string $route): bool
    {
        $sensitiveRoutes = [
            'oauth.clients.store',
            'oauth.clients.client-credentials.store',
            'oauth.clients.regenerate-secret',
            'oauth.clients.force-delete',
            'oauth.clients.audit',
        ];

        return in_array($route, $sensitiveRoutes);
    }

    /**
     * Détecte les activités suspectes
     */
    private function checkSuspiciousActivity(array $auditData): void
    {
        // Log simple pour les tentatives d'accès non autorisées
        if ($auditData['status_code'] === 403 && $this->isSensitiveEndpoint($auditData['route'])) {
            Log::channel('oauth_audit')->alert('Suspicious OAuth Activity - Forbidden Access to Sensitive Endpoint', [
                'route' => $auditData['route'],
                'ip_address' => $auditData['ip_address'],
                'user_id' => $auditData['user_id'],
                'client_id' => $auditData['client_id'],
                'timestamp' => $auditData['timestamp'],
            ]);
        }

        // Log des échecs d'authentification répétés
        if ($auditData['status_code'] === 401) {
            Log::channel('oauth_audit')->warning('OAuth Authentication Failure', [
                'ip_address' => $auditData['ip_address'],
                'route' => $auditData['route'],
                'user_agent' => $auditData['headers']['user-agent'][0] ?? 'Unknown',
                'timestamp' => $auditData['timestamp'],
            ]);
        }
    }
}