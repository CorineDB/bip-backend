<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Projet;
use App\Models\User;
use App\Models\Champ;
use App\Models\ChampProjet;
use App\Models\Fichier;
use App\Models\Commentaire;
use App\Traits\HashableId;

class Rapport extends Model
{
    use HasFactory, SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'rapports';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at', 'date_soumission', 'date_validation'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'projet_id',
        'parent_id',
        'type',
        'statut',
        'intitule',
        'checklist_suivi',
        'info_cabinet_etude',
        'recommandation',
        'date_soumission',
        'soumis_par_id',
        'date_validation',
        'validateur_id',
        'commentaire_validation',
        'decision',
        // Checklists pour préfaisabilité
        'checklist_suivi_rapport_prefaisabilite',
        'checklists_mesures_adaptation_haut_risque',
        // Checklists pour faisabilité
        'checklist_etude_faisabilite_marche',
        'checklist_etude_faisabilite_economique',
        'checklist_etude_faisabilite_technique',
        'checklist_etude_faisabilite_organisationnelle_et_juridique',
        'checklist_suivi_analyse_faisabilite_financiere',
        'checklist_suivi_etude_analyse_impact_environnementale_et_sociale',
        'checklist_suivi_assurance_qualite_rapport_etude_faisabilite',

        'investissement_initial',
        'van',
        'tri',
        'flux_tresorerie',
        'duree_vie',
        'taux_actualisation'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'flux_tresorerie' => 'array',
        'investissement_initial' => 'float',
        'van' => 'float',
        'tri' => 'float',
        'duree_vie' => 'integer',
        'taux_actualisation' => 'float',
        'checklist_suivi' => 'array',
        'info_cabinet_etude' => 'array',
        'decision' => 'array',
        // Checklists pour préfaisabilité
        'checklist_suivi_rapport_prefaisabilite' => 'array',
        'checklists_mesures_adaptation_haut_risque' => 'array',
        // Checklists pour faisabilité
        'checklist_etude_faisabilite_marche' => 'array',
        'checklist_etude_faisabilite_economique' => 'array',
        'checklist_etude_faisabilite_technique' => 'array',
        'checklist_etude_faisabilite_organisationnelle_et_juridique' => 'array',
        'checklist_suivi_analyse_faisabilite_financiere' => 'array',
        'checklist_suivi_etude_analyse_impact_environnementale_et_sociale' => 'array',
        'checklist_suivi_assurance_qualite_rapport_etude_faisabilite' => 'array',
        'date_soumission' => 'datetime',
        'date_validation' => 'datetime',
    ];

    /**
     * Relation avec le projet
     */
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'projet_id');
    }

    /**
     * Relation avec l'utilisateur qui a soumis le rapport
     */
    public function soumisPar()
    {
        return $this->belongsTo(User::class, 'soumis_par_id');
    }

    /**
     * Relation avec l'utilisateur qui a rédigé le rapport
     */
    public function redigerPar()
    {
        return $this->belongsTo(User::class, 'rediger_par_id');
    }


    /**
     * Relation avec le validateur
     */
    public function validateur()
    {
        return $this->belongsTo(User::class, 'validateur_id');
    }

    /**
     * Scope pour filtrer par type de rapport
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeStatut($query, $statut)
    {
        return $query->where('statut', $statut);
    }

    /**
     * Scope pour les rapports de préfaisabilité
     */
    public function scopePrefaisabilite($query)
    {
        return $query->where('type', 'prefaisabilite');
    }

    /**
     * Scope pour les rapports de faisabilité
     */
    public function scopeFaisabilite($query)
    {
        return $query->where('type', 'faisabilite');
    }

    /**
     * Scope pour les rapports de faisabilité
     */
    public function scopeFaisabilitePreliminaire($query)
    {
        return $query->where('type', 'faisabilite-preliminaire');
    }

    /**
     * Scope pour les évaluations ex-ante
     */
    public function scopeEvaluationExAnte($query)
    {
        return $query->where('type', 'evaluation_ex_ante');
    }

    /**
     * Relation avec le rapport parent
     */
    public function parent()
    {
        return $this->belongsTo(Rapport::class, 'parent_id');
    }

    /**
     * Relation avec les rapports enfants
     */
    public function enfants()
    {
        return $this->hasMany(Rapport::class, 'parent_id');
    }

    /**
     * Scope pour récupérer le dernier rapport d'un type pour un projet
     */
    public function scopeDernierRapport($query, $projetId, $type = null)
    {
        $query = $query->where('projet_id', $projetId);

        if ($type) {
            $query = $query->where('type', $type);
        }

        return $query->latest('created_at');
    }

    /**
     * Vérifier si c'est le dernier rapport du projet pour ce type
     */
    public function estDernierRapport()
    {
        $dernierRapport = static::dernierRapport($this->projet_id, $this->type)->first();
        return $dernierRapport && $dernierRapport->id === $this->id;
    }

    /**
     * Relation many-to-many avec les champs (pour les valeurs de la checklist de suivi)
     */
    public function champs()
    {
        return $this->morphToMany(Champ::class, 'projetable', 'champs_projet', 'projetable_id', 'champId')
            ->using(ChampProjet::class)
            ->withPivot(['valeur', 'commentaire', 'id'])
            ->withTimestamps();
    }

    /**
     * Relation avec les fichiers du rapport
     */
    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichier_attachable', 'fichier_attachable_type', 'fichier_attachable_id');
    }

    /**
     * Relation avec les fichiers de rapport (PDF, documents)
     */
    public function fichiersRapport()
    {
        return $this->fichiers()->where('categorie', 'rapport');
    }

    /**
     * Relation avec les procès-verbaux
     */
    public function procesVerbaux()
    {
        return $this->fichiers()->where('categorie', 'proces-verbal');
    }

    /**
     * Relation avec les documents annexes
     */
    public function documentsAnnexes()
    {
        return $this->fichiers()->where('categorie', 'annexe');
    }

    /**
     * Relation polymorphique avec les commentaires
     */
    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    /**
     * Obtenir le rapport avec ses fichiers, recommandations et commentaires
     */
    public function avecFichiersRecommandationsEtCommentaires()
    {
        $this->load([
            'fichiers.uploadedBy',
            'commentaires.auteur',
            'validateur',
            'soumisPar',
            'redigerPar'
        ]);

        return [
            'rapport' => $this,
            'fichiers' => $this->fichiers,
            'recommandations' => $this->recommandation,
            'commentaires' => [
                'validation' => $this->commentaire_validation,
                'commentaires' => $this->commentaires,
                'fichiers' => $this->fichiers->pluck('commentaire', 'id')->filter()
            ]
        ];
    }

    /**
     * Relation avec tous les rapports de préfaisabilité du projet
     */
    public function historique_des_rapports_prefaisabilite()
    {
        return $this->hasMany(Rapport::class, 'projet_id', 'projet_id')
            //->where('id', '!=', $this->id)
            ->where('type', 'prefaisabilite')
            ->where('statut', '<>', 'brouillon')->whereHas("enfants")
            ->orderBy('created_at', 'desc');
    }

    /**
     * Relation avec toutes les évaluations des rapports de préfaisabilité du projet
     */
    public function historique_des_evaluations_rapports_prefaisabilite()
    {
        return $this->historique_des_rapports_prefaisabilite()->with(["evaluations" => function ($query) {
            $query->where("type_evaluation", "rapport-prefaisabilite")->where('statut', '=', 1)->whereHas("childEvaluations")->orderBy("created_at", "desc");
        }]);
    }

    /**
     * Relation avec tous les rapports de faisabilité du projet
     */
    public function historique_des_rapports_faisabilite()
    {
        return $this->hasMany(Rapport::class, 'projet_id', 'projet_id')
            //->where('id', '!=', $this->id)
            ->where('type', 'faisabilite')
            ->where('statut', '<>', 'brouillon')->whereHas("enfants")
            ->orderBy('created_at', 'desc');
    }

    /**
     * Relation avec toutes les évaluations des rapports de faisabilité du projet
     */
    public function historique_des_evaluations_rapports_faisabilite()
    {
        return $this->historique_des_rapports_faisabilite()->with(["evaluations" => function ($query) {
            $query->where("type_evaluation", "rapport-faisabilite")->where('statut', '=', 1)->whereHas("childEvaluations")->orderBy("created_at", "desc");
        }]);
    }

    /**
     * Get all reports of the same type for the same project (excluding current).
     * Returns a HasMany relationship for all reports with same projet_id and type.
     * Ordered by: created_at DESC (most recent first)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function historique()
    {
        return $this->hasMany(Rapport::class, 'projet_id', 'projet_id')
            ->where('type', $this->type)
            ->where('statut', '<>', 'brouillon')->whereHas("enfants")
            ->orderBy('created_at', 'desc');
    }

    /**
     * Get all reports of the same type for the same project including current report.
     * Returns a Collection of all reports sharing the same projet_id and type.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllRapportsHistorique()
    {
        return static::where('projet_id', $this->projet_id)
            ->where('type', $this->type)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get the iteration number of this report in the complete history.
     *
     * @return int
     */
    public function getIterationNumber()
    {
        $historique = $this->getAllRapportsHistorique();
        $position = $historique->search(function ($rapport) {
            return $rapport->id === $this->id;
        });

        return $position !== false ? $position + 1 : 1;
    }

    /**
     * Calcule la Valeur Actuelle Nette (VAN) du projet.
     *
     * @return float|null La VAN du projet, ou null si les données sont insuffisantes.
     */
    public function calculerVAN(): ?float
    {
        // Utiliser le taux d'actualisation du projet, ou 10% par défaut.
        $tauxActualisation = (float) ($this->taux_actualisation ?? 0.1);

        // I0: L'investissement initial
        $investissementInitial = (float) $this->investissement_initial;

        // CFt: Les flux de trésorerie nets (automatiquement casté en array par Eloquent)
        $fluxTresorerie = $this->flux_tresorerie;

        if (!is_array($fluxTresorerie) || empty($fluxTresorerie)) {
            return null; // Pas de flux de trésorerie pour calculer la VAN
        }

        $van = 0;
        foreach ($fluxTresorerie as $flux) {
            // Vérifier que les clés 't' et 'CFt' existent
            if (isset($flux['t']) && isset($flux['CFt'])) {
                $t = (int) $flux['t'];
                $cft = (float) $flux['CFt'];
                $van += $cft / pow(1 + $tauxActualisation, $t);
            }
        }

        return $van - $investissementInitial;
    }

    /**
     * Calcule le Taux de Rentabilité Interne (TRI) du projet.
     *
     * Le TRI est le taux d'actualisation qui annule la VAN du projet.
     * Cette méthode utilise une approche itérative (méthode de Newton-Raphson) pour trouver le TRI.
     *
     * @return float|null Le TRI en format décimal (ex: 0.15 pour 15%), ou null si le calcul échoue.
     */
    public function calculerTRI(): ?float
    {
        // Paramètres de l'algorithme numérique
        $estimationInitiale = 0.1; // 10%
        $maxIterations = 100;
        $precision = 1e-5;

        // Prépare le tableau des flux de trésorerie : [-I0, CF1, CF2, ...]
        $investissementInitial = (float) $this->investissement_initial;
        $fluxTresorerie = $this->flux_tresorerie; // Casté en array par Eloquent

        if ($investissementInitial <= 0 || !is_array($fluxTresorerie) || empty($fluxTresorerie)) {
            return null; // Données insuffisantes pour le calcul
        }

        // Le premier flux (t=0) est l'investissement initial (négatif)
        $cashFlows = [-$investissementInitial];
        foreach ($fluxTresorerie as $flux) {
            if (isset($flux['CFt'])) {
                $cashFlows[] = (float) $flux['CFt'];
            }
        }

        // Implémentation de la méthode de Newton-Raphson pour trouver la racine.
        $tri = $estimationInitiale;

        for ($i = 0; $i < $maxIterations; $i++) {
            $van = 0;
            $deriveeVan = 0;

            foreach ($cashFlows as $t => $cf) {
                if ((1 + $tri) == 0 && $t > 0) return null; // Evite la division par zéro
                if ((1 + $tri) != 0) {
                    $van += $cf / pow(1 + $tri, $t);
                    if ($t > 0) {
                        $deriveeVan -= $t * $cf / pow(1 + $tri, $t + 1);
                    }
                }
            }

            if (abs($van) < $precision) {
                return $tri; // Solution trouvée avec la précision souhaitée
            }

            // Éviter la division par zéro pour la dérivée
            if ($deriveeVan == 0) {
                return null; // Le calcul ne peut pas continuer
            }

            // Prochaine itération de Newton-Raphson
            $tri = $tri - $van / $deriveeVan;
        }

        return null; // Pas de convergence après le nombre maximum d'itérations
    }

    public function evaluations()
    {
        // Déterminer le type d'évaluation selon le type de rapport
        // Types de rapport: 'prefaisabilite', 'faisabilite', 'evaluation_ex_ante'
        $typeEvaluation = match ($this->type) {
            'prefaisabilite' => 'validation-etude-prefaisabilite',
            'faisabilite' => 'validation-etude-faisabilite',
            'evaluation_ex_ante' => 'validation-final-evaluation-ex-ante',
            'faisabilite-preliminaire' => 'controle-qualite-rapport-faisabilite-preliminaire',
            default => $this->type
        };

        return $this->morphMany(Evaluation::class, 'projetable')->where('type_evaluation', $typeEvaluation);
    }

    public function evaluationTermine()
    {
        $typeEvaluation = match ($this->type) {
            'prefaisabilite' => 'validation-etude-prefaisabilite',
            'faisabilite' => 'validation-etude-faisabilite',
            'evaluation_ex_ante' => 'validation-final-evaluation-ex-ante',
            'faisabilite-preliminaire' => 'controle-qualite-rapport-faisabilite-preliminaire',
            default => $this->type
        };

        return $this->evaluations()->evaluationTermine($typeEvaluation)->first();
    }

    public function evaluationEnCours()
    {
        $typeEvaluation = match ($this->type) {
            'prefaisabilite' => 'validation-etude-prefaisabilite',
            'faisabilite' => 'validation-etude-faisabilite',
            'evaluation_ex_ante' => 'validation-final-evaluation-ex-ante',
            'faisabilite-preliminaire' => 'controle-qualite-rapport-faisabilite-preliminaire',
            default => $this->type
        };

        return $this->evaluations()->evaluationsEnCours($typeEvaluation)->first();
    }

    public function evaluationParent()
    {
        $typeEvaluation = match ($this->type) {
            'prefaisabilite' => 'validation-etude-prefaisabilite',
            'faisabilite' => 'validation-etude-faisabilite',
            'evaluation_ex_ante' => 'validation-final-evaluation-ex-ante',
            'faisabilite-preliminaire' => 'controle-qualite-rapport-faisabilite-preliminaire',
            default => $this->type
        };

        return $this->evaluations()->evaluationParent($typeEvaluation)->first();
    }
}
