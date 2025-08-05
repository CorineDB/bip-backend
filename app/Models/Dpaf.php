<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Dpaf extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dpaf';

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
        'nom', 'slug', 'description', 'id_ministere'
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
                'slug' => time() . '::' . $model->slug
            ]);

            if (method_exists($model, 'user')) {
                // Exemple : $model->user()->delete();
            }
        });
    }

    /**
     * Get the roles that belong to this DPAF (polymorphic).
     */
    public function roles()
    {
        return $this->morphMany(Role::class, 'roleable');
    }

    /**
     * Get the users that belong to this DPAF (polymorphic).
     */
    public function membres()
    {
        return $this->morphMany(User::class, 'profilable');
    }

    /**
     * Get the groups that belong to this DPAF (polymorphic).
     */
    public function groupesUtilisateur()
    {
        return $this->morphMany(GroupeUtilisateur::class, 'profilable');
    }

    public function user()
    {
        return $this->morphOne(User::class, 'profilable');
    }

    public function ministere()
    {
        return $this->belongsTo(Organisation::class, 'id_ministere');
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
        $this->attributes['slug'] = $this->generateUniqueSlug($value);
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