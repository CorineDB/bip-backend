<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class Tdr extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     */
    protected $table = 'tdrs';

    /**
     * The attributes that should be mutated to dates.
     */
    protected $dates = [
        'date_soumission',
        'date_evaluation',
        'date_validation',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'projet_id',
        'parent_id',
        'type',
        'statut',
        'resume',
        'date_soumission',
        'soumis_par_id',
        'rediger_par_id',
        'date_evaluation',
        'date_validation',
        'evaluateur_id',
        'validateur_id',
        'evaluations_detaillees',
        'termes_de_reference',
        'commentaire_evaluation',
        'commentaire_validation',
        'decision_validation',
        'commentaire_decision',
        'resultats_evaluation',
        'nombre_passe',
        'nombre_retour',
        'nombre_non_accepte'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'evaluations_detaillees'    => 'array',
        'termes_de_reference'       => 'array',
        'date_soumission'           => 'datetime',
        'date_evaluation'           => 'datetime',
        'date_validation'           => 'datetime',
        'nombre_passe'              => 'integer',
        'nombre_retour'             => 'integer',
        'nombre_non_accepte'        => 'integer'
    ];

    /**
     * Default values for attributes.
     */
    protected $attributes = [
        'statut' => 'brouillon',
        'nombre_passe' => 0,
        'nombre_retour' => 0,
        'nombre_non_accepte' => 0
    ];

    // Relations Eloquent

    /**
     * Relation avec le projet
     */
    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    /**
     * Relation avec l'utilisateur qui a soumis le TDR
     */
    public function soumisPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'soumis_par_id');
    }

    /**
     * Relation avec l'utilisateur qui a rédigé le TDR
     */
    public function redigerPar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rediger_par_id');
    }

    /**
     * Relation avec l'évaluateur
     */
    public function evaluateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluateur_id');
    }

    /**
     * Relation avec le validateur
     */
    public function validateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }

    /**
     * Relation avec le TDR parent (version précédente)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Tdr::class, 'parent_id');
    }

    /**
     * Relation avec les TDR enfants (versions suivantes)
     */
    public function versions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Tdr::class, 'parent_id');
    }

    /**
     * Relation avec les fichiers attachés au TDR
     */
    public function fichiers(): MorphMany
    {
        return $this->morphMany(Fichier::class, 'fichierAttachable', 'fichier_attachable_type', 'fichier_attachable_id')
            ->active()
            ->ordered();
    }

    /**
     * Relation avec les commentaires sur le TDR
     */
    public function commentaires(): MorphMany
    {
        return $this->morphMany(Commentaire::class, 'commentaireable', 'commentaireable_type', 'commentaireable_id')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Obtenir les fichiers TDR spécifiquement
     */
    public function fichiersTdr(): MorphMany
    {
        return $this->fichiers()->byCategorie('tdr');
    }

    /**
     * Obtenir les fichiers rapport
     */
    public function fichiersRapport(): MorphMany
    {
        return $this->fichiers()->byCategorie('tdr-prefaisabilite');
    }

    /**
     * Relation many-to-many avec les champs (pour les valeurs saisies)
     */
    public function champs(): MorphToMany
    {
        return $this->morphToMany(Champ::class, 'projetable', 'champs_projet', 'projetable_id', 'champId')
            ->using(ChampProjet::class)
            ->withPivot(['valeur', 'commentaire', 'id'])
            ->withTimestamps();
    }

    /**
     * Relation avec les évaluations du TDR
     */
    public function evaluations(): MorphMany
    {
        return $this->morphMany(Evaluation::class, 'projetable', 'projetable_type', 'projetable_id');
    }

    /**
     * Obtenir l'évaluation parent (précédente) pour ce TDR
     */
    public function evaluationTerminer()
    {
        return $this->evaluations()->evaluationTermine('tdr-prefaisabilite')->first();
    }

    /**
     * Obtenir l'évaluation en cours pour ce TDR
     */
    public function evaluationEnCours()
    {
        return $this->evaluations()->evaluationsEnCours('tdr-prefaisabilite')->first();
    }

    /**
     * Obtenir l'évaluation parent (précédente) pour ce TDR
     */
    public function evaluationParent()
    {
        return $this->evaluations()->evaluationParent('tdr-prefaisabilite')->first();
    }

    // Scopes pour les filtres

    /**
     * Scope pour filtrer par type de TDR
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeWithStatus(Builder $query, string $statut): Builder
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les TDRs de préfaisabilité
     */
    public function scopePrefaisabilite(Builder $query): Builder
    {
        return $query->where('type', 'prefaisabilite');
    }

    /**
     * Scope pour les TDRs de faisabilité
     */
    public function scopeFaisabilite(Builder $query): Builder
    {
        return $query->where('type', 'faisabilite');
    }

    /**
     * Scope pour les TDRs soumis
     */
    public function scopeSoumis(Builder $query): Builder
    {
        return $query->where('statut', 'soumis');
    }

    /**
     * Scope pour les TDRs en évaluation
     */
    public function scopeEnEvaluation(Builder $query): Builder
    {
        return $query->where('statut', 'en_evaluation');
    }

    /**
     * Scope pour les TDRs validés
     */
    public function scopeValides(Builder $query): Builder
    {
        return $query->where('statut', 'valide');
    }

    /**
     * Scope pour filtrer par projet
     */
    public function scopeForProjet(Builder $query, int $projetId): Builder
    {
        return $query->where('projet_id', $projetId);
    }

    /**
     * Scope pour filtrer par évaluateur
     */
    public function scopeEvaluatedBy(Builder $query, int $evaluateurId): Builder
    {
        return $query->where('evaluateur_id', $evaluateurId);
    }

    /**
     * Scope pour filtrer par période de soumission
     */
    public function scopeSoumisEntre(Builder $query, Carbon $debut, Carbon $fin): Builder
    {
        return $query->whereBetween('date_soumission', [$debut, $fin]);
    }

    /**
     * Scope pour recherche globale
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function($q) use ($search) {
            $q->where('resume', 'LIKE', "%{$search}%")
              ->orWhere('commentaire_evaluation', 'LIKE', "%{$search}%")
              ->orWhere('commentaire_validation', 'LIKE', "%{$search}%")
              ->orWhere('commentaire_decision', 'LIKE', "%{$search}%")
              ->orWhereHas('projet', function($subQ) use ($search) {
                  $subQ->where('titre_projet', 'LIKE', "%{$search}%");
              });
        });
    }

    // Accesseurs et Mutateurs

    /**
     * Vérifier si le TDR peut être soumis
     */
    public function peutEtreSoumis(): bool
    {
        return $this->statut === 'brouillon' &&
               !empty($this->resume);
    }

    /**
     * Vérifier si le TDR peut être évalué
     */
    public function peutEtreEvalue(): bool
    {
        return $this->statut === 'soumis';
    }

    /**
     * Vérifier si le TDR peut être validé
     */
    public function peutEtreValide(): bool
    {
        return $this->statut === 'en_evaluation'/*  &&
               !empty($this->evaluations_detaillees) */;
    }

    /**
     * Obtenir le pourcentage de réussite de l'évaluation
     */
    public function getPourcentageReussiteAttribute(): float
    {
        $total = $this->nombre_passe + $this->nombre_retour + $this->nombre_non_accepte;
        return $total > 0 ? ($this->nombre_passe / $total) * 100 : 0;
    }

    // Méthodes pour gérer les fichiers

    /**
     * Vérifier si le TDR a des fichiers attachés
     */
    public function hasFichiers(): bool
    {
        return $this->fichiers()->count() > 0;
    }

    /**
     * Obtenir le fichier TDR principal
     */
    public function getFichierTdrPrincipal(): ?Fichier
    {
        return $this->fichiersTdr()->first();
    }

    /**
     * Obtenir le nombre total de fichiers
     */
    public function getNombreFichiersAttribute(): int
    {
        return $this->fichiers()->count();
    }

    /**
     * Obtenir la taille totale des fichiers en bytes
     */
    public function getTailleTotaleFichiersAttribute(): int
    {
        return $this->fichiers()->sum('taille');
    }

    // Méthodes pour gérer les commentaires

    /**
     * Ajouter un commentaire au TDR
     */
    public function ajouterCommentaire(string $texte, int $commentateurId): Commentaire
    {
        return $this->commentaires()->create([
            'commentaire' => $texte,
            'commentateurId' => $commentateurId,
            'date' => now()
        ]);
    }

    /**
     * Obtenir le dernier commentaire
     */
    public function getDernierCommentaire(): ?Commentaire
    {
        return $this->commentaires()->first();
    }

    /**
     * Obtenir le nombre de commentaires
     */
    public function getNombreCommentairesAttribute(): int
    {
        return $this->commentaires()->count();
    }

    /**
     * Vérifier si le TDR a des commentaires
     */
    public function hasCommentaires(): bool
    {
        return $this->commentaires()->count() > 0;
    }

    /**
     * Obtenir les commentaires récents (7 derniers jours)
     */
    public function getCommentairesRecents(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->commentaires()->recents()->get();
    }
}