<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EvaluationCritere extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'evaluation_criteres';

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
        'note',
        'evaluateur_id',
        'notation_id',
        'critere_id',
        'categorie_critere_id',
        'evaluation_id'
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
            // Clean up related data if needed
        });
    }

    /**
     * Get the evaluation that owns this critere evaluation.
     */
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluation_id');
    }

    /**
     * Get the evaluateur (user) who made this evaluation.
     */
    public function evaluateur()
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }

    /**
     * Get the notation for this evaluation.
     */
    public function notation()
    {
        return $this->belongsTo(Notation::class, 'notation_id');
    }

    /**
     * Get the critere being evaluated.
     */
    public function critere()
    {
        return $this->belongsTo(Critere::class, 'critere_id');
    }

    /**
     * Get the categorie critere.
     */
    public function categorieCritere()
    {
        return $this->belongsTo(CategorieCritere::class, 'categorie_critere_id');
    }
}