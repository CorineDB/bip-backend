<?php

namespace App\Models;

use App\Enums\EnumTypeOrganisation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Organisation extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organisations';

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
        'nom', 'slug', 'description', 'type', 'parentId'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => EnumTypeOrganisation::class,
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
     * Get the parent organisation.
     */
    public function parent()
    {
        return $this->belongsTo(Organisation::class, 'parentId');
    }

    /**
     * Get the ministere organisation (self if already ministry, or parent ministry).
     */
    public function ministere()
    {
        // Si cette organisation est déjà un ministère, se retourner elle-même
        if ($this->type === EnumTypeOrganisation::MINISTERE) {
            return $this->newQuery()->where('id', $this->id);
        }
        
        // Sinon, chercher le ministère parent récursivement
        $current = $this;
        while ($current->parent) {
            $current = $current->parent;
            if ($current->type === EnumTypeOrganisation::MINISTERE) {
                return $this->newQuery()->where('id', $current->id);
            }
        }
        
        // Aucun ministère trouvé
        return $this->newQuery()->whereRaw('1 = 0');
    }

    /**
     * Get the child organisations.
     */
    public function children()
    {
        return $this->hasMany(Organisation::class, 'parentId');
    }

    /**
     * Get the personnes for the organisation.
     */
    public function personnes()
    {
        return $this->hasMany(Personne::class, 'organismeId');
    }

    /**
     * Get the organisation that owns the role (polymorphic).
     */
    public function roles()
    {
        return $this->morphMany(Role::class, 'roleable');
    }

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
        $this->attributes['nom'] = addslashes($value); // Escape value with backslashes
        $this->attributes['slug'] = str_replace(' ', '-', strtolower($value));
    }

    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getNomAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }

    public function user()
    {
        return $this->morphOne(User::class, 'profilable');
    }

    /**
     * Get the users that belong to this organisaiton (polymorphic).
     */
    public function membres()
    {
        return $this->morphMany(User::class, 'profilable');
    }
}