<?php

namespace App\Models;

use App\Traits\HashableId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserPermissionScope extends Model
{
    use SoftDeletes, HashableId;

    protected $table = 'user_permission_scope';

    protected $fillable = [
        'user_id',
        'permission_id',
        'object_type',
        'workflow_stage',
        'scopeable_type',
        'scopeable_id',
        'is_active',
        'expires_at',
        'granted_by',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'expires_at' => 'datetime'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permission()
    {
        return $this->belongsTo(Permission::class);
    }

    public function grantedBy()
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function scopeable()
    {
        return $this->morphTo();
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

    public function scopeForObject($query, $objectType)
    {
        return $query->where('object_type', $objectType);
    }

    public function scopeForWorkflowStage($query, $stage)
    {
        return $query->where('workflow_stage', $stage);
    }

    public function scopeForScope($query, $scopeableType, $scopeableId)
    {
        return $query->where('scopeable_type', $scopeableType)
                    ->where('scopeable_id', $scopeableId);
    }

    // Méthodes utiles
    public function isExpired()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isValidForStage($stage)
    {
        return is_null($this->workflow_stage) || $this->workflow_stage === $stage;
    }
}
