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

class Rapport extends Model
{
    use HasFactory, SoftDeletes;

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
        'checklist_suivi_assurance_qualite_rapport_etude_faisabilite'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
     * Relation avec tous les TDRs du projet
     */
    public function historique_des_notes_conceptuelle()
    {
        /* return $this->hasMany(NoteConceptuelle::class, 'projetId', 'projetId')
                    ->where('id', '!=', $this->id)
                    ->orderBy('created_at', 'desc'); */
        if ($this->projet) {
            return $this->projet->notes_conceptuelle()->where("id", "!=", $this->id)->orderBy("created_at", "desc");
        }
        // Return a query builder that will result in an empty set if no projet is associated
        return $this->hasMany(NoteConceptuelle::class, 'projetId', 'projetId')->whereRaw('0 = 1');
    }

    /**
     * Relation avec tous les TDRs du projet
     */
    public function historique_des_evaluations_notes_conceptuelle()
    {
        return $this->historique_des_notes_conceptuelle()->with(["evaluations" => function($query){
            $query/* ->evaluationTermine("note-conceptuelle")->first() */;
        }]);
    }
}
