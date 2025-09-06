<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FichierPermission extends Model
{
    use SoftDeletes;

    protected $table = 'fichier_permissions';

    protected $fillable = [
        'fichier_id',
        'user_id',
        'permission_type', // 'view', 'edit', 'download', 'share'
        'granted_by',
        'expires_at',
        'is_active'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    // Relations
    public function fichier()
    {
        return $this->belongsTo(Fichier::class);
    }

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
}