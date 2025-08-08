<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evaluation extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'evaluations';

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
        'type_evaluation',
        'date_debut_evaluation',
        'date_fin_evaluation',
        'valider_le',
        'projetable_type',
        'projetable_id',
        'evaluateur_id',
        'valider_par',
        'commentaire',
        'evaluation',
        'resultats_evaluation',
        'statut',
        'id_evaluation'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'created_at'            => 'datetime:Y-m-d',
        'updated_at'            => 'datetime:Y-m-d H:i:s',
        'deleted_at'            => 'datetime:Y-m-d H:i:s',
        'date_debut_evaluation' => 'datetime',
        'date_fin_evaluation'   => 'datetime',
        'valider_le'            => 'datetime',
        'evaluation'            => 'array',
        'resultats_evaluation'  => 'array',
        'statut'                => 'integer'
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
     * Get the projetable entity that the evaluation belongs to.
     */
    public function projetable()
    {
        return $this->morphTo();
    }

    /**
     * Get the projetable entity that the evaluation belongs to.
     */
    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentaireable');
    }

    /**
     * Get the evaluateur (evaluator).
     */
    public function evaluateur()
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }

    /**
     * Get the validator.
     */
    public function validator()
    {
        return $this->belongsTo(User::class, 'valider_par');
    }

    /**
     * Get the parent evaluation (if this is a sub-evaluation).
     */
    public function parentEvaluation()
    {
        return $this->belongsTo(Evaluation::class, 'id_evaluation');
    }

    /**
     * Get child evaluations (sub-evaluations).
     */
    public function childEvaluations()
    {
        return $this->hasMany(Evaluation::class, 'id_evaluation');
    }

    /**
     * Get the evaluation criteria for this evaluation.
     */
    public function evaluationCriteres()
    {
        return $this->hasMany(EvaluationCritere::class, 'evaluation_id');
    }

    /**
     * Get all evaluateurs for this evaluation through evaluation_criteres.
     */
    public function evaluateurs()
    {
        return $this->belongsToMany(User::class, 'evaluation_criteres', 'evaluation_id', 'evaluateur_id')
            ->withPivot('critere_id', 'note', 'notation_id', 'categorie_critere_id')
            ->withTimestamps()
            ->distinct();
    }

    /**
     * Get evaluation criteres grouped by evaluateur.
     */
    public function getEvaluationsByUser()
    {
        return $this->evaluationCriteres()
            ->with(['evaluateur', 'critere', 'notation', 'categorieCritere'])
            ->get()
            ->groupBy('evaluateur_id');
    }

    /**
     * Calculate aggregated scores by critere with ponderation.
     */
    public function getAggregatedScores()
    {
        return $this->evaluationCriteres()
            ->with(['critere', 'notation', 'evaluateur'])
            ->get()
            ->groupBy('critere_id')
            ->map(function ($critereEvaluations) {
                $notes = $critereEvaluations->pluck('notation.valeur')->filter();
                $critere = $critereEvaluations->first()->critere;
                $moyenne_evaluateurs = $notes->average();

                return [
                    'critere' => $critere,
                    'ponderation' => $critere->ponderation,
                    'ponderation_pct' => $critere->ponderation . '%',
                    'moyenne_evaluateurs' => $moyenne_evaluateurs,
                    'score_pondere' => $moyenne_evaluateurs * ($critere->ponderation / 100),
                    'total_evaluateurs' => $critereEvaluations->count(),
                    'evaluateurs' => $critereEvaluations->pluck('evaluateur.nom')->filter()->toArray()
                ];
            });
    }

    /**
     * Calculate aggregated scores by critere with ponderation.
     */
    public function getAMCAggregatedScores()
    {
        return $this->evaluationCriteres()
            ->with(['critere', 'notation', 'evaluateur'])
            ->get()
            ->groupBy('critere_id')
            ->map(function ($critereEvaluations) {
                $critere = $critereEvaluations->first()->critere;
                if (str_contains(strtolower($critere->intitule ?? ''), 'impact climatique')) {
                    $notes = $critereEvaluations->pluck('note')->filter();
                } else {
                    $notes = $critereEvaluations->pluck('notation.valeur')->filter();
                }

                $moyenne_evaluateurs = $notes->average();

                return [
                    'critere' => $critere,
                    'ponderation' => $critere->ponderation,
                    'ponderation_pct' => $critere->ponderation . '%',
                    'moyenne_evaluateurs' => $moyenne_evaluateurs,
                    'score_pondere' => $moyenne_evaluateurs * ($critere->ponderation / 100),
                    'total_evaluateurs' => $critereEvaluations->count(),
                    'evaluateurs' => $critereEvaluations->pluck('evaluateur.nom')->filter()->toArray()
                ];
            });
    }

    /**
     * Get evaluation status based on the statut field.
     */
    public function getStatutTextAttribute(): string
    {
        return match ($this->statut) {
            -1 => 'En attente',
            0 => 'En cours',
            1 => 'Terminée',
            default => 'Inconnu'
        };
    }

    /**
     * Check if evaluation is pending.
     */
    public function isPending(): bool
    {
        return $this->statut === -1;
    }

    /**
     * Check if evaluation is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->statut === 0;
    }

    /**
     * Check if evaluation is completed.
     */
    public function isCompleted(): bool
    {
        return $this->statut === 1;
    }

    /**
     * Mark evaluation as in progress.
     */
    public function markInProgress(): bool
    {
        return $this->update(['statut' => 0]);
    }

    /**
     * Mark evaluation as completed.
     */
    public function markCompleted(): bool
    {
        return $this->update(['statut' => 1]);
    }

    /**
     * Mark evaluation as pending.
     */
    public function markPending(): bool
    {
        return $this->update(['statut' => -1]);
    }
}
