<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Personne extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'personnes';

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
        'prenom',
        'poste',
        'organismeId'
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
        'organismeId',
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the organisation that owns the personne.
     */
    public function organisation()
    {
        return $this->belongsTo(Organisation::class, 'organismeId');
    }

    /**
     * Get the user for the personne.
     */
    public function user()
    {
        return $this->hasOne(User::class, 'personneId');
    }

    /**
     * Get the user's ministere de tutelle through organisation.
     */
    /*public function ministere()
    {
        if (!$this->organisation) {
            return $this->belongsTo(Organisation::class, 'organismeId')->whereRaw('1 = 0');
        }

        return $this->organisation->ministere();
    }*/

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            // Delete associated user when personne is deleted
            if ($model->user) {
                $model->user()->delete();
            }
        });
    }

    /**
     * Récupérer le ministère de tutelle de la personne via la hiérarchie de son organisation.
     *
     */
    public function ministere()
    {
        if (!$this->organisation) {
            // Pas d'organisation, on retourne un builder vide pour éviter erreur
            return Organisation::whereRaw('1 = 0');
        }

        // On retourne directement le Builder renvoyé par la méthode ministere() de Organisation
        return $this->organisation->ministere();
    }

    public function getMinistereAttribute()
    {
        return $this->ministere()->first();
    }

    /**
     * Scope pour récupérer toutes les personnes rattachées à un ministère donné,
     * y compris celles associées à ses organismes enfants (structures hiérarchiques descendantes).
     *
     * @param Builder $query
     * @param int $idMinistere L'ID du ministère racine
     * @return Builder
     */
    public function scopeMinisteres(Builder $query, $idMinistere)
    {
        return $query->whereHas("organisation", function ($query) use ($idMinistere) {
            $query->descendantsFromMinistere($idMinistere);
        });
    }
}
