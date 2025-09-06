<?php

namespace App\Traits;

use App\Models\UserPermissionScope;
use App\Models\Permission;

trait HasScopedPermissions
{
    public function permissionScopes()
    {
        return $this->hasMany(UserPermissionScope::class, 'user_id');
    }

    public function activePermissionScopes()
    {
        return $this->permissionScopes()->active();
    }

    /**
     * Vérifier si l'utilisateur a une permission scopée sur un objet
     */
    public function hasScopedPermission(string $permissionName, $object, ?string $workflowStage = null): bool
    {
        $permission = Permission::where('slug', $permissionName)->first();
        if (!$permission) {
            return false;
        }

        $query = $this->activePermissionScopes()
            ->where('permission_id', $permission->id)
            ->where('object_type', get_class($object));

        if ($workflowStage) {
            $query->where(function($q) use ($workflowStage) {
                $q->whereNull('workflow_stage')
                  ->orWhere('workflow_stage', $workflowStage);
            });
        }

        // Vérifier les scopes hiérarchiques
        return $query->where(function($q) use ($object) {
            $this->buildScopeQuery($q, $object);
        })->exists();
    }

    /**
     * Construire la requête pour vérifier les scopes hiérarchiques
     */
    private function buildScopeQuery($query, $object)
    {
        // Scope direct sur l'objet
        $query->orWhere(function($q) use ($object) {
            $q->where('scopeable_type', get_class($object))
              ->where('scopeable_id', $object->id);
        });

        // Scopes hiérarchiques selon le type d'objet
        if (method_exists($object, 'getHierarchicalScopes')) {
            $scopes = $object->getHierarchicalScopes();
            
            foreach ($scopes as $scopeType => $scopeId) {
                if ($scopeId) {
                    $query->orWhere(function($q) use ($scopeType, $scopeId) {
                        $q->where('scopeable_type', $scopeType)
                          ->where('scopeable_id', $scopeId);
                    });
                }
            }
        }

        // Permissions globales (sans scope spécifique)
        $query->orWhere(function($q) {
            $q->whereNull('scopeable_type')
              ->whereNull('scopeable_id');
        });
    }

    /**
     * Accorder une permission scopée
     */
    public function grantScopedPermission(
        string $permissionName,
        string $objectType,
        $scopeable = null,
        ?string $workflowStage = null,
        ?\Carbon\Carbon $expiresAt = null,
        ?int $grantedBy = null
    ): UserPermissionScope {
        $permission = Permission::where('slug', $permissionName)->firstOrFail();

        return $this->permissionScopes()->create([
            'permission_id' => $permission->id,
            'object_type' => $objectType,
            'workflow_stage' => $workflowStage,
            'scopeable_type' => $scopeable ? get_class($scopeable) : null,
            'scopeable_id' => $scopeable ? $scopeable->id : null,
            'expires_at' => $expiresAt,
            'granted_by' => $grantedBy ?? auth()->id(),
            'is_active' => true
        ]);
    }

    /**
     * Révoquer une permission scopée
     */
    public function revokeScopedPermission(string $permissionName, string $objectType, $scopeable = null): bool
    {
        $permission = Permission::where('slug', $permissionName)->first();
        if (!$permission) {
            return false;
        }

        $query = $this->permissionScopes()
            ->where('permission_id', $permission->id)
            ->where('object_type', $objectType);

        if ($scopeable) {
            $query->where('scopeable_type', get_class($scopeable))
                  ->where('scopeable_id', $scopeable->id);
        } else {
            $query->whereNull('scopeable_type')
                  ->whereNull('scopeable_id');
        }

        return $query->delete();
    }

    /**
     * Obtenir tous les objets accessibles pour un type donné
     */
    public function getAccessibleObjects(string $objectClass, ?string $permissionName = null, ?string $workflowStage = null)
    {
        $baseQuery = app($objectClass)->query();

        // Si permission spécifique demandée
        if ($permissionName) {
            $permission = Permission::where('slug', $permissionName)->first();
            if (!$permission) {
                return $baseQuery->whereRaw('1=0'); // Aucun résultat
            }

            $scopeQuery = $this->activePermissionScopes()
                ->where('permission_id', $permission->id)
                ->where('object_type', $objectClass);

            if ($workflowStage) {
                $scopeQuery->where(function($q) use ($workflowStage) {
                    $q->whereNull('workflow_stage')
                      ->orWhere('workflow_stage', $workflowStage);
                });
            }

            $scopes = $scopeQuery->get();

            if ($scopes->isEmpty()) {
                return $baseQuery->whereRaw('1=0'); // Aucun résultat
            }

            // Construire les conditions de filtrage
            $baseQuery->where(function($q) use ($scopes, $objectClass) {
                foreach ($scopes as $scope) {
                    $this->addObjectFilterCondition($q, $scope, $objectClass);
                }
            });
        }

        return $baseQuery;
    }

    /**
     * Ajouter les conditions de filtrage selon le scope
     */
    private function addObjectFilterCondition($query, $scope, $objectClass)
    {
        if (!$scope->scopeable_type || !$scope->scopeable_id) {
            // Permission globale - tous les objets de ce type
            return;
        }

        $query->orWhere(function($q) use ($scope, $objectClass) {
            // Logique selon le type de scope
            switch ($scope->scopeable_type) {
                case 'App\Models\Organisation':
                    // Filtrer par ministère/agence/institution
                    if (method_exists(app($objectClass), 'whereHasMinistry')) {
                        $q->whereHasMinistry($scope->scopeable_id);
                    }
                    break;
                    
                case 'App\Models\Secteur':
                    // Filtrer par secteur/sous-secteur
                    if (method_exists(app($objectClass), 'whereHasSector')) {
                        $q->whereHasSector($scope->scopeable_id);
                    }
                    break;
                    
                case 'App\Models\CategorieProjet':
                    // Filtrer par catégorie
                    $q->where('categorie_projet_id', $scope->scopeable_id);
                    break;
                    
                case $objectClass:
                    // Scope direct sur l'objet
                    $q->where('id', $scope->scopeable_id);
                    break;
            }
        });
    }
}