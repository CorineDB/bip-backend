<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class IdeeProjetFormulaire extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'fiche_idee_id',
        'utilisateur_id', 
        'statut',
        'phase',
        'donnees_saisies',
        'metadata',
        'submitted_at',
        'validated_at',
        'commentaires'
    ];

    protected $casts = [
        'donnees_saisies' => 'json',
        'metadata' => 'json',
        'submitted_at' => 'datetime',
        'validated_at' => 'datetime'
    ];

    /**
     * Relation avec la fiche idée (template du formulaire)
     */
    public function ficheIdee(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'fiche_idee_id');
    }

    /**
     * Relation avec l'utilisateur qui a créé l'idée
     */
    public function utilisateur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    /**
     * Scopes pour filtrer par statut
     */
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    public function scopePhase($query, $phase)
    {
        return $query->where('phase', $phase);
    }

    /**
     * Obtenir une valeur spécifique des données saisies
     */
    public function getDonnee(string $attribut)
    {
        return $this->donnees_saisies[$attribut] ?? null;
    }

    /**
     * Définir une valeur dans les données saisies
     */
    public function setDonnee(string $attribut, $valeur): void
    {
        $donnees = $this->donnees_saisies;
        $donnees[$attribut] = $valeur;
        $this->donnees_saisies = $donnees;
    }

    /**
     * Vérifier si l'idée peut être modifiée
     */
    public function peutEtreModifiee(): bool
    {
        return in_array($this->statut, ['brouillon', 'rejetee']);
    }

    /**
     * Soumettre l'idée pour validation
     */
    public function soumettre(): bool
    {
        if ($this->statut === 'brouillon') {
            $this->update([
                'statut' => 'soumise',
                'phase' => 'validation',
                'submitted_at' => now()
            ]);
            return true;
        }
        return false;
    }
}
