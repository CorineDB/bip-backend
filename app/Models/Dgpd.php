<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Dgpd extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dgpd';

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
        'description'
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

        /*static::creating(function ($dgpd) {
            $dgpd->roles()->firstOrCreate(
                [
                    'slug' => 'analyste-dgpd',
                    'roleable_type' => Dgpd::class,
                    'roleable_id' => $dgpd->id,
                ],
                [
                    'nom' => 'Analyste DGPD',
                    'description' => 'Analyste des idées projet ou projets',
                ]
            );
        });*/

        static::deleting(function ($model) {
            $model->update([
                'nom' => time() . '::' . $model->nom,
                'slug' => time() . '::' . $model->slug
            ]);

            if (method_exists($model, 'user')) {
                // Exemple : $model->user()->delete();
            }
        });
    }

    /**
     * Get the roles that belong to this DGPD (polymorphic).
     */
    public function roles()
    {
        return $this->morphMany(Role::class, 'roleable');
    }

    /**
     * Get the membres that belong to this DGPD (polymorphic).
     */
    public function membres()
    {
        return $this->morphMany(User::class, 'profilable');
    }

    /**
     * Get the groups that belong to this DGPD (polymorphic).
     */
    public function groupesUtilisateur()
    {
        return $this->morphMany(GroupeUtilisateur::class, 'profilable');
    }

    public function user()
    {
        return $this->morphOne(User::class, 'profilable');
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

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $this->generateUniqueSlug($value ?? Str::slug($this->attributes['nom']));
    }

    private function generateUniqueSlug($name)
    {
        $baseSlug = str_replace(' ', '-', strtolower($name));
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
