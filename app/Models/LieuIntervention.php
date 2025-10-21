<?php

namespace App\Models;

use App\Traits\HashableId;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LieuIntervention extends Pivot
{
    use HasFactory, SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'lieux_intervention_projets';

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
        'departementId',
        'communeId',
        'villageId',
        'arrondissementId',
        'projetable_id',
        'projetable_type'
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
     * Get the departement when type is departement
     */
    public function departement()
    {
        return $this->belongsTo(Departement::class, 'departementId');
    }

    /**
     * Get the commune when type is commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'communeId');
    }

    /**
     * Get the arrondissement when type is arrondissement
     */
    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class, 'arrondissementId');
    }

    /**
     * Get the village when type is village
     */
    public function village()
    {
        return $this->belongsTo(Village::class, 'villageId');
    }

    /**
     * Get all projects associated with this location
     */
    public function ideesProjet()
    {
        return $this->morphedByMany(IdeeProjet::class, 'projetable', 'lieux_intervention_projets')
            ->withTimestamps();
    }

    /**
     * Get all projects associated with this location
     */
    public function projets()
    {
        return $this->morphedByMany(Projet::class, 'projetable', 'lieux_intervention_projets')
            ->withTimestamps();
    }
}
