<?php

namespace App\Traits;

use App\Models\ResourcePermission;
use App\Models\User;

trait HasResourcePermissions
{
    public function permissions()
    {
        return $this->morphMany(ResourcePermission::class, 'permissionable');
    }

    public function activePermissions()
    {
        return $this->permissions()->active();
    }

    // Vérifier si un utilisateur a une permission
    public function hasPermission(User $user, string $permissionType): bool
    {
        // Propriétaire a toutes les permissions
        if ($this->isOwner($user)) {
            return true;
        }

        // Vérifier permissions spécifiques
        return $this->activePermissions()
            ->forUser($user->id)
            ->byType($permissionType)
            ->exists();
    }

    // Accorder une permission
    public function grantPermission(User $user, string $permissionType, ?User $grantedBy = null, ?\Carbon\Carbon $expiresAt = null): ResourcePermission
    {
        return $this->permissions()->create([
            'user_id' => $user->id,
            'permission_type' => $permissionType,
            'granted_by' => $grantedBy?->id ?? auth()->id(),
            'expires_at' => $expiresAt,
            'is_active' => true
        ]);
    }

    // Révoquer une permission
    public function revokePermission(User $user, string $permissionType): bool
    {
        return $this->permissions()
            ->forUser($user->id)
            ->byType($permissionType)
            ->delete();
    }

    // Vérifier si l'utilisateur est propriétaire
    protected function isOwner(User $user): bool
    {
        // À adapter selon votre structure
        if (property_exists($this, 'uploaded_by')) {
            return $this->uploaded_by === $user->id;
        }
        
        if (property_exists($this, 'created_by')) {
            return $this->created_by === $user->id;
        }

        return false;
    }

    // Obtenir tous les fichiers/dossiers partagés avec un utilisateur
    public static function sharedWith(User $user)
    {
        return static::whereHas('permissions', function($query) use ($user) {
            $query->active()->forUser($user->id);
        });
    }

    // Obtenir les permissions d'un utilisateur sur cette ressource
    public function getUserPermissions(User $user)
    {
        return $this->activePermissions()
            ->forUser($user->id)
            ->get()
            ->pluck('permission_type')
            ->toArray();
    }
}