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
        'commentaire',
        'evaluateur_id',
        'notation_id',
        'critere_id',
        'categorie_critere_id',
        'evaluation_id',
        'is_auto_evaluation',
        'est_archiver'
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
        'is_auto_evaluation' => 'boolean',
        'est_archiver' => 'boolean',
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

    /**
     * Scope to filter by evaluation.
     */
    public function scopeForEvaluation($query, $evaluationId)
    {
        return $query->where('evaluation_id', $evaluationId);
    }

    /**
     * Scope to filter by evaluateur.
     */
    public function scopeByEvaluateur($query, $evaluateurId)
    {
        return $query->where('evaluateur_id', $evaluateurId);
    }

    /**
     * Scope to filter by critere.
     */
    public function scopeByCritere($query, $critereId)
    {
        return $query->where('critere_id', $critereId);
    }

    /**
     * Scope to get completed evaluations only.
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('notation_id')
                    ->where('note', '!=', 'En attente');
    }

    /**
     * Scope to get pending evaluations only.
     */
    public function scopePending($query)
    {
        return $query->whereNull('notation_id')
                    ->orWhere('note', 'En attente');
    }

    /**
     * Check if this evaluation is completed.
     */
    public function isCompleted(): bool
    {
        return !is_null($this->notation_id) && $this->note !== 'En attente';
    }

    /**
     * Check if this evaluation is pending.
     */
    public function isPending(): bool
    {
        return is_null($this->notation_id) || $this->note === 'En attente';
    }

    /**
     * Get the numeric value from notation.
     */
    public function getNumericValue(): ?float
    {
        if (!$this->notation) {
            return null;
        }

        return is_numeric($this->notation->valeur) ? (float) $this->notation->valeur : null;
    }

    /**
     * Mark this evaluation as completed with a notation.
     */
    public function markCompleted($notationId, $note = null, $commentaire = null): bool
    {
        return $this->update([
            'notation_id' => $notationId,
            'note' => $note ?? $this->note,
        ]);
    }

    /**
     * Get evaluation status as string.
     */
    public function getStatusAttribute(): string
    {
        if ($this->isCompleted()) {
            return 'completed';
        }

        return 'pending';
    }

    /**
     * Scope to filter auto evaluations.
     */
    public function scopeAutoEvaluation($query)
    {
        return $query->where('is_auto_evaluation', true);
    }

    /**
     * Scope to filter manual evaluations.
     */
    public function scopeManualEvaluation($query)
    {
        return $query->where('is_auto_evaluation', false);
    }

    /**
     * Scope to filter manual evaluations.
     */
    public function scopeEvaluationExterne($query)
    {
        return $query->where('is_auto_evaluation', false);
    }

    /**
     * Scope to filter archived evaluations.
     */
    public function scopeArchived($query)
    {
        return $query->where('est_archiver', true);
    }

    /**
     * Scope to filter active evaluations.
     */
    public function scopeActive($query)
    {
        return $query->where('est_archiver', false);
    }

    /**
     * Check if this is an auto evaluation.
     */
    public function isAutoEvaluation(): bool
    {
        return $this->is_auto_evaluation === true;
    }

    /**
     * Check if this evaluation is archived.
     */
    public function isArchived(): bool
    {
        return $this->est_archiver === true;
    }

    /**
     * Archive this evaluation.
     */
    public function archive(): bool
    {
        return $this->update(['est_archiver' => true]);
    }

    /**
     * Unarchive this evaluation.
     */
    public function unarchive(): bool
    {
        return $this->update(['est_archiver' => false]);
    }
}