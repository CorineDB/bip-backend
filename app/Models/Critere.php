<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Critere extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = "criteres";

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ["created_at", "updated_at", "deleted_at"];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "intitule", "ponderation", "commentaire", "is_mandatory", "est_general", "categorie_critere_id"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "created_at" => "datetime:Y-m-d",
        "updated_at" => "datetime:Y-m-d H:i:s",
        "deleted_at" => "datetime:Y-m-d H:i:s",
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

        static::deleting(function ($critere) {
            // Modifier l'intitulé pour éviter les conflits lors de futures créations
            $critere->update([
                'intitule' => time() . '::' . $critere->intitule,
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
     * Get the notations
     */
    public function notations()
    {
        return $this->hasMany(Notation::class, 'critere_id');
    }

    /**
     * Get the evaluation criteria for this evaluation.
     */
    public function critereEvaluations()
    {
        return $this->hasMany(EvaluationCritere::class, 'evaluation_id');
    }
}
