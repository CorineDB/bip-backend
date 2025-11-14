<?php

namespace App\Models;

use App\Traits\HashableId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class Tdr extends Model
{
    use HasFactory, SoftDeletes, HashableId;

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
        'nombre_non_accepte',
        'numero_contrat',
        'numero_dossier',
        'accept_term',
        'canevas_appreciation_tdr',
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
        'nombre_non_accepte'        => 'integer',
        'accept_term'               => 'boolean',
        'canevas_appreciation_tdr'  => 'array'
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
        $typeEvaluation = match ($this->type) {
            'prefaisabilite' => 'tdr-prefaisabilite',
            'faisabilite' => 'tdr-faisabilite',
            default => 'tdr-' . $this->type
        };

        return $this->fichiers()->byCategorie($typeEvaluation);
        return $this->fichiers()->byCategorie('tdr-prefaisabilite');
    }

    /**
     * Obtenir les fichiers appreciation tdr
     */
    public function fichierAppreciationTdr(): MorphOne
    {
        $typeEvaluation = match ($this->type) {
            'prefaisabilite' => 'appreciation_tdr_prefaisabilite',
            'faisabilite' => 'appreciation_tdr_faisabilite',
            default => 'appreciation_tdr_' . $this->type
        };

        return $this->morphOne(
            Fichier::class,
            'fichierAttachable',
            'fichier_attachable_type',
            'fichier_attachable_id'
        )
            ->byCategorie($typeEvaluation)->orderBy("created_at", "desc");
        return $this->fichiers()->orderBy("created_at", "desc")->byCategorie($typeEvaluation);
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
     * Relation avec tous les TDRs de préfaisabilité du projet
     */
    public function historique_des_tdrs_prefaisabilite()
    {
        return $this->hasMany(Tdr::class, 'projet_id', 'projet_id')
            //->where('id', '!=', $this->id)
            ->where('statut', '<>', 'brouillon')->whereHas("versions")
            ->where('type', 'prefaisabilite')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Relation avec toutes les évaluations des TDRs de préfaisabilité du projet
     */
    public function historique_des_evaluations_tdrs_prefaisabilite()
    {
        return $this->historique_des_tdrs_prefaisabilite()->with(["evaluations" => function ($query) {
            $query->where("type_evaluation", "tdr-prefaisabilite")->where('statut', 1)->whereHas("childEvaluations")->orderBy("created_at", "desc");
        }]);
    }

    /**
     * Relation avec tous les TDRs de faisabilité du projet
     */
    public function historique_des_tdrs_faisabilite()
    {
        return $this->hasMany(Tdr::class, 'projet_id', 'projet_id')
            /*->where('id', '!=', $this->id)
                    ->where('statut', 1)*/
            ->where('statut', '<>', 'brouillon')->whereHas("versions")
            ->where('type', 'faisabilite')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Relation avec toutes les évaluations des TDRs de faisabilité du projet
     */
    public function historique_des_evaluations_tdrs_faisabilite()
    {
        return $this->historique_des_tdrs_faisabilite()->with(["evaluations" => function ($query) {
            $query->where("type_evaluation", "tdr-faisabilite")->where('statut', 1)->whereHas("childEvaluations")->orderBy("created_at", "desc");
        }]);
    }

    /**
     * Relation avec les évaluations du TDR de prefaisabilite
     */
    public function evaluationsPrefaisabilite(): MorphMany
    {
        return $this->evaluations()->where('type_evaluation', 'tdr-prefaisabilite');
    }

    /**
     * Obtenir l'évaluation parent (précédente) pour ce TDR
     */
    public function evaluationPrefaisabiliteTerminer()
    {
        return $this->evaluationsPrefaisabilite()->evaluationTermine('tdr-prefaisabilite')->first();
    }

    /**
     * Obtenir l'évaluation en cours pour ce TDR
     */
    public function evaluationPrefaisabiliteEnCours()
    {
        return $this->evaluationsPrefaisabilite()->evaluationsEnCours('tdr-prefaisabilite')->first();
    }

    /**
     * Obtenir l'évaluation parent (précédente) pour ce TDR
     */
    public function evaluationPrefaisabiliteParent()
    {
        return $this->evaluationsPrefaisabilite()->evaluationParent('tdr-prefaisabilite')->first();
    }

    /**
     * Relation avec les évaluations du TDR de faisabilite
     */
    public function evaluationsFaisabilite(): MorphMany
    {
        return $this->evaluations()->where('type_evaluation', 'tdr-faisabilite');
    }

    /**
     * Obtenir l'évaluation parent (précédente) pour ce TDR
     */
    public function evaluationFaisabiliteTerminer()
    {
        return $this->evaluationsFaisabilite()->evaluationTermine('tdr-faisabilite')->first();
    }

    /**
     * Obtenir l'évaluation en cours pour ce TDR
     */
    public function evaluationFaisabiliteEnCours()
    {
        return $this->evaluationsFaisabilite()->evaluationsEnCours('tdr-faisabilite')->first();
    }

    /**
     * Obtenir l'évaluation parent (précédente) pour ce TDR
     */
    public function evaluationFaisabiliteParent()
    {
        return $this->evaluationsFaisabilite()->evaluationParent('tdr-faisabilite')->first();
    }

    /**
     * Obtenir l'évaluation parent (précédente) pour ce TDR
     */
    public function evaluationTerminer()
    {
        $typeEvaluation = match ($this->type) {
            'prefaisabilite' => 'tdr-prefaisabilite',
            'faisabilite' => 'tdr-faisabilite',
            default => 'tdr-' . $this->type
        };

        return $this->evaluations()->evaluationTermine($typeEvaluation)->first();
        return $this->evaluations()->evaluationTermine('tdr-prefaisabilite')->first();
    }

    /**
     * Obtenir l'évaluation en cours pour ce TDR
     */
    public function evaluationEnCours()
    {
        $typeEvaluation = match ($this->type) {
            'prefaisabilite' => 'tdr-prefaisabilite',
            'faisabilite' => 'tdr-faisabilite',
            default => 'tdr-' . $this->type
        };

        return $this->evaluations()->evaluationsEnCours($typeEvaluation)->first();
    }

    /**
     * Obtenir l'évaluation parent (précédente) pour ce TDR
     */
    public function evaluationParent()
    {
        $typeEvaluation = match ($this->type) {
            'prefaisabilite' => 'tdr-prefaisabilite',
            'faisabilite' => 'tdr-faisabilite',
            default => 'tdr-' . $this->type
        };

        return $this->evaluations()->evaluationParent($typeEvaluation)->first();
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
        return $query->where(function ($q) use ($search) {
            $q->where('resume', 'LIKE', "%{$search}%")
                ->orWhere('commentaire_evaluation', 'LIKE', "%{$search}%")
                ->orWhere('commentaire_validation', 'LIKE', "%{$search}%")
                ->orWhere('commentaire_decision', 'LIKE', "%{$search}%")
                ->orWhereHas('projet', function ($subQ) use ($search) {
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
