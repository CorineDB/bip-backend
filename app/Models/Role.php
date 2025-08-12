<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Role extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'roles';

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
        'nom', 'slug', 'description', 'roleable_id', 'roleable_type'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
        'updated_at', 'deleted_at'
    ];

    /**
     * Get the owning roleable model.
     */
    public function roleable()
    {
        return $this->morphTo();
    }

    /**
     * The permissions that belong to the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'roleId', 'permissionId');
    }

    /**
     * The users that belong to the role.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'roleId');
    }

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($role) {
            // Liste des rôles critiques à ne pas supprimer
            $rolesCritiques = [
                'super-admin',
                'dgpd',
                'dpaf',
                'organisation',
                'responsable-projet',
                'responsable-hierachique',
                'analyste-dgpd',
            ];

            if (in_array($role->slug, $rolesCritiques)) {
                throw new \Exception("Le rôle critique « {$role->slug} » ne peut pas être supprimé.");
            }

            $role->update([
                'nom' => time() . '::' . $role->nom,
                'slug' => time() . '::' . $role->slug,
            ]);
        });
    }

    /**
     *
     *
     * @param  string  $value
     * @return void
     */
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = Str::ucfirst(trim($value)); // Escape value with backslashes
        if(isset($this->attributes['slug'])){
            $this->attributes['slug'] = $value ?? Str::slug($this->attributes['nom']);
        }
    }

    /**
     *
     *
     * @param  string  $value
     * @return void
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value ?? Str::slug($this->attributes['nom']);
    }

    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getNomAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }
}