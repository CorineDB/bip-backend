<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckScopedPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @param  string|null  $objectType
     * @param  string|null  $workflowStage
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission, ?string $objectType = null, ?string $workflowStage = null)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentification requise'
            ], 401);
        }

        // Si un objet spécifique est dans la route (ex: /projets/{projet})
        $object = $this->resolveObject($request, $objectType);
        
        if ($object) {
            // Vérification sur objet spécifique
            if (!$user->hasScopedPermission($permission, $object, $workflowStage)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission insuffisante pour cette action sur cet objet'
                ], 403);
            }
        } else {
            // Vérification générale de la permission
            $hasPermission = $user->activePermissionScopes()
                ->whereHas('permission', function($q) use ($permission) {
                    $q->where('slug', $permission);
                })
                ->when($objectType, function($q) use ($objectType) {
                    $q->where('object_type', $objectType);
                })
                ->when($workflowStage, function($q) use ($workflowStage) {
                    $q->where(function($subQ) use ($workflowStage) {
                        $subQ->whereNull('workflow_stage')
                             ->orWhere('workflow_stage', $workflowStage);
                    });
                })
                ->exists();

            if (!$hasPermission) {
                return response()->json([
                    'success' => false,
                    'message' => 'Permission insuffisante pour cette action'
                ], 403);
            }
        }

        return $next($request);
    }

    /**
     * Résoudre l'objet depuis les paramètres de route
     */
    private function resolveObject(Request $request, ?string $objectType)
    {
        if (!$objectType) {
            return null;
        }

        // Mapping des types vers les paramètres de route
        $routeParams = [
            'App\Models\Projet' => 'projet',
            'App\Models\IdeeProjet' => 'ideeProjet',
            'App\Models\Tdr' => 'tdr',
            'App\Models\Rapport' => 'rapport',
        ];

        $paramName = $routeParams[$objectType] ?? null;
        
        if (!$paramName || !$request->route($paramName)) {
            return null;
        }

        return $request->route($paramName);
    }
}