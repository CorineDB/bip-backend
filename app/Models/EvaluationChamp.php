<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationChamp extends Pivot
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'evaluation_champs';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'date_note'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'evaluationId',
        'champId',
        'note',
        'commentaires',
        'date_note',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date_note' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relation avec l'évaluation
     */
    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class, 'evaluationId');
    }

    /**
     * Relation avec le champ
     */
    public function champ()
    {
        return $this->belongsTo(Champ::class, 'champId');
    }

    /**
     * Relation polymorphique avec les commentaires
     */
    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentaireable');
    }
}
