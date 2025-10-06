<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Village extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'villages';

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
        'code', 'nom', 'slug', 'arrondissementId', 'latitude', 'longitude'
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
        'arrondissementId', 'updated_at', 'deleted_at'
    ];

    /**
     * Get the arrondissement that owns the village.
     */
    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class, 'arrondissementId');
    }

    /**
     * Get the commune through arrondissement.
     */
    public function commune()
    {
        return $this->hasOneThrough(Commune::class, Arrondissement::class, 'id', 'id', 'arrondissementId', 'communeId');
    }

    /**
     * Get the departement through arrondissement and commune.
     */
    public function departement()
    {
        return $this->arrondissement->commune->departement ?? null;
    }

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->update([
                'code' => time() . '::' . $model->code,
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
}
