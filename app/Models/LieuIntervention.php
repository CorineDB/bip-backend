<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class LieuIntervention extends Pivot
{
    use HasFactory, SoftDeletes;

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
        'coordonnees_gps' => 'array',
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
     * Get the specific location (polymorphic relation)
     */
    public function lieu()
    {
        return $this->morphTo('lieu', 'type_lieu', 'lieu_id');
    }

    /**
     * Get the departement when type is departement
     */
    public function departement()
    {
        return $this->belongsTo(Departement::class, 'lieu_id')->where('type_lieu', 'departement');
    }

    /**
     * Get the commune when type is commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'lieu_id')->where('type_lieu', 'commune');
    }

    /**
     * Get the arrondissement when type is arrondissement
     */
    public function arrondissement()
    {
        return $this->belongsTo(Arrondissement::class, 'lieu_id')->where('type_lieu', 'arrondissement');
    }

    /**
     * Get the village when type is village
     */
    public function village()
    {
        return $this->belongsTo(Village::class, 'lieu_id')->where('type_lieu', 'village');
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

    /**
     * Scope to filter by administrative level
     */
    public function scopeNiveauAdministratif($query, $niveau)
    {
        return $query->where('niveau_administratif', $niveau);
    }

    /**
     * Scope to filter by location type
     */
    public function scopeTypeLieu($query, $type)
    {
        return $query->where('type_lieu', $type);
    }

    /**
     * Get the full administrative path
     */
    public function getCheminAdministratifAttribute()
    {
        $lieu = $this->lieu;

        if (!$lieu) {
            return $this->nom;
        }

        switch ($this->type_lieu) {
            case 'village':
                return $lieu->arrondissement->commune->departement->nom . ' > ' .
                       $lieu->arrondissement->commune->nom . ' > ' .
                       $lieu->arrondissement->nom . ' > ' .
                       $lieu->nom;
            case 'arrondissement':
                return $lieu->commune->departement->nom . ' > ' .
                       $lieu->commune->nom . ' > ' .
                       $lieu->nom;
            case 'commune':
                return $lieu->departement->nom . ' > ' . $lieu->nom;
            case 'departement':
                return $lieu->nom;
            default:
                return $this->nom;
        }
    }
}