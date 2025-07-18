<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, SoftDeletes, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider', 'provider_user_id', 'username', 'email', 'status', 
        'is_email_verified', 'email_verified_at', 'password', 'personneId', 
        'roleId', 'last_connection', 'ip_address'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_email_verified' => 'boolean',
        'email_verified_at' => 'timestamp',
        'last_connection' => 'timestamp',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'personneId', 'roleId', 'updated_at', 'deleted_at'
    ];

    /**
     * Get the personne that owns the user.
     */
    public function personne()
    {
        return $this->belongsTo(Personne::class, 'personneId');
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

    /**
     * Get the user's organisation through personne.
     */
    public function organisation()
    {
        return $this->hasOneThrough(Organisation::class, Personne::class, 'id', 'id', 'personneId', 'organismeId');
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->role->permissions()->where('slug', $permission)->exists();
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->role->permissions()->whereIn('slug', $permissions)->exists();
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $userPermissions = $this->role->permissions()->pluck('slug')->toArray();
        return count(array_intersect($permissions, $userPermissions)) === count($permissions);
    }

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->update([
                'username' => time() . '::' . $model->username,
                'email' => time() . '::' . $model->email,
            ]);
        });
    }
}