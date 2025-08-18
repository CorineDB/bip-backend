<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Auth\Access\AuthorizationException;

class AuthorizeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $ability  L'action à autoriser (create, update, delete, view, viewAny, manage)
     * @param  string  $model    La classe du modèle (ex: App\Models\IdeeProjet)
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $ability, string $model = null)
    {
        try {
            // Si un modèle spécifique est passé, on l'utilise
            if ($model) {
                $modelClass = $model;
            } else {
                // Sinon, on essaie de déduire le modèle à partir de la route
                $modelClass = $this->getModelFromRoute($request);
            }

            // Si l'ability est "manage", on détermine l'action selon la méthode HTTP et la route
            if ($ability === 'manage') {
                $ability = $this->getAbilityFromRequest($request);
            }

            // Pour les actions sur une instance spécifique (update, delete, view)
            if (in_array($ability, ['update', 'delete', 'view']) && $request->route()) {
                $modelId = $this->getModelIdFromRoute($request);
                if ($modelId) {
                    $modelInstance = $modelClass::findOrFail($modelId);
                    $request->user()->authorize($ability, $modelInstance);
                } else {
                    $request->user()->authorize($ability, $modelClass);
                }
            } else {
                // Pour les actions sur la classe (create, viewAny)
                $request->user()->authorize($ability, $modelClass);
            }

        } catch (AuthorizationException $e) {
            return response()->json([
                'statut' => 'error',
                'message' => "Vous n'avez pas l'autorisation d'effectuer cette action sur cette ressource",
                'errors' => [],
                'statutCode' => Response::HTTP_FORBIDDEN
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }

    /**
     * Tenter de déduire la classe du modèle à partir de la route
     */
    private function getModelFromRoute(Request $request): string
    {
        $routeName = $request->route()->getName();
        
        // Mapping des routes vers les modèles
        $routeModelMap = [
            'api.idees-projet' => 'App\Models\IdeeProjet',
            'api.projets' => 'App\Models\Projet',
            'api.categories-document' => 'App\Models\CategorieDocument',
            'api.documents' => 'App\Models\Document',
            'api.categories-critere' => 'App\Models\CategorieCritere',
        ];

        foreach ($routeModelMap as $routePrefix => $modelClass) {
            if (str_starts_with($routeName, $routePrefix)) {
                return $modelClass;
            }
        }

        throw new \InvalidArgumentException("Impossible de déterminer le modèle pour la route: {$routeName}");
    }

    /**
     * Extraire l'ID du modèle à partir des paramètres de route
     */
    private function getModelIdFromRoute(Request $request)
    {
        $route = $request->route();
        
        // Paramètres courants utilisés dans vos routes
        $possibleParams = ['id', 'idee_projet', 'projet', 'categorie_document', 'document', 'categorie_critere'];
        
        foreach ($possibleParams as $param) {
            if ($route->hasParameter($param)) {
                return $route->parameter($param);
            }
        }

        return null;
    }

    /**
     * Déterminer l'ability basé sur la méthode HTTP et l'action de la route
     */
    private function getAbilityFromRequest(Request $request): string
    {
        $method = $request->method();
        $routeName = $request->route()->getName();
        
        // Déterminer l'action selon la méthode HTTP et le nom de la route
        if ($method === 'GET') {
            // Si la route contient un ID (show) ou non (index)
            $modelId = $this->getModelIdFromRoute($request);
            return $modelId ? 'view' : 'viewAny';
        } elseif ($method === 'POST') {
            return 'create';
        } elseif (in_array($method, ['PUT', 'PATCH'])) {
            return 'update';
        } elseif ($method === 'DELETE') {
            return 'delete';
        }

        // Par défaut, utiliser viewAny si on ne peut pas déterminer
        return 'viewAny';
    }
}