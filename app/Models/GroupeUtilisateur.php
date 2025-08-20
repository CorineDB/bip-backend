<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class GroupeUtilisateur extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'groupes_utilisateur';

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
        'nom',
        'slug',
        'description',
        'profilable_id',
        'profilable_type'
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
        // Exemple : 'programmeId', 'updated_at', 'deleted_at'
    ];

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->update([
                'nom' => time() . '::' . $model->nom,
                'slug' => time() . '::' . $model->slug,
            ]);

            if (method_exists($model, 'user')) {
                // Exemple : $model->user()->delete();
            }
        });
    }

    /**
     * Get the profile entity (polymorphic relation).
     */
    public function profilable()
    {
        return $this->morphTo();
    }

    /**
     * Get the users that belong to this group.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'groupe_utilisateur_users', 'groupeUtilisateurId', 'userId')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    /**
     * Get the roles that belong to this group.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'groupe_utilisateur_roles', 'groupeUtilisateurId', 'roleId')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

    /**
     * Get the permissions that belong to this group.
     */
    public function permissions()
    {
        return $this->belongsToMany(Role::class, 'groupe_utilisateur_permissions', 'groupeUtilisateurId', 'permissionId')
            ->withTimestamps()
            ->withPivot('deleted_at');
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

        if(!isset($this->attributes['slug'])){
            $this->attributes['slug'] = $this->attributes['nom'];
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
    public function getNomAttribute($value)
    {
        return ucfirst(str_replace('\\', ' ', $value));
    }
}
