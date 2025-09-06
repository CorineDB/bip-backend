<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResourcePermission extends Model
{
    use SoftDeletes;

    protected $table = 'resource_permissions';

    protected $fillable = [
        'permissionable_id',
        'permissionable_type', // Fichier, Dossier, Projet, etc.
        'user_id',
        'permission_type', // 'view', 'edit', 'download', 'share', 'delete'
        'granted_by',
        'expires_at',
        'is_active',
        'inherit_to_children' // pour les dossiers
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'inherit_to_children' => 'boolean'
    ];

    // Relation polymorphe
    public function permissionable()
    {
        return $this->morphTo();
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                    ->where(function($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('permission_type', $type);
    }
}