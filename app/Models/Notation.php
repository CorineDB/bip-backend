<?php

namespace App\Models;

use App\Traits\HashableId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notation extends Model
{
    use HasFactory, SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notations';

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
        "libelle", "valeur", "commentaire", "secteur_id", "critere_id", "categorie_critere_id"
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

        static::deleting(function ($notation) {
            // Modifier le libellé et la valeur pour éviter les conflits lors de futures créations
            $notation->update([
                'libelle' => time() . '::' . $notation->libelle,
                'valeur' => time() . '::' . $notation->valeur,
            ]);
        });
    }

    /**
     * Get the critere
     */
    public function categorie_critere()
    {
        return $this->belongsTo(CategorieCritere::class, 'categorie_critere_id');
    }

    /**
     * Get the critere
     */
    public function critere()
    {
        return $this->belongsTo(Critere::class, 'critere_id');
    }

    /**
     * Get the secteur
     */
    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteur_id');
    }

    /**
     * Get evaluation criteres grouped by evaluateur.
     */
    public function notationsParSecteur($query)
    {
        return $query()
            ->with(['secteur'])
            ->get()
            ->groupBy('secteur_id');
    }
}
