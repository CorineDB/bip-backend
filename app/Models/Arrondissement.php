<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Arrondissement extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'arrondissements';

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
        'code', 'nom', 'slug', 'communeId', 'latitude', 'longitude'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
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
        'communeId', 'updated_at', 'deleted_at'
    ];

    /**
     * Get the commune that owns the arrondissement.
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'communeId');
    }

    /**
     * Get the villages for the arrondissement.
     */
    public function villages()
    {
        return $this->hasMany(Village::class, 'arrondissementId');
    }

    /**
     * Get the departement through commune.
     */
    public function departement()
    {
        return $this->hasOneThrough(Departement::class, Commune::class, 'id', 'id', 'communeId', 'departementId');
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
                'nom' => time() . '::' . $model->nom,
                'slug' => time() . '::' . $model->slug,
            ]);
        });
    }
}
