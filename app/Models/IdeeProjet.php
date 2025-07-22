<?php

namespace App\Models;

use App\Enums\StatutIdee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IdeeProjet extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'idee_projets';

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
        // Exemple : 'nom', 'programmeId'
        "identifiant_bip",
        "identifiant_sigfp",
        "est_coherent",
        "ficheIdee",
        "statut",
        "phase",
        "sous_phase",
        "decision",
        "sigle",
        "type_projet",
        "parties_prenantes",
        "objectifs_specifiques",
        "resultats_attendus",
        "isdeleted",
        "body_projet",
        "cout_dollar_americain",
        "cout_euro",
        "date_debut_etude",
        "date_fin_etude",
        "date_prevue_demarrage",
        "date_effective_demarrage",
        "cout_dollar_canadien",
        "risques_immediats",
        "sommaire",
        "objectif_general",
        "conclusions",
        "description",
        "constats_majeurs",
        "public_cible",
        "estimation_couts",
        "description_decision",
        "impact_environnement",
        "aspect_organisationnel",
        "description_extrants",
        "caracteristiques",
        "score_climatique",
        "score_amc",
        "duree",
        "description_projet",
        "origine",
        "situation_desiree",
        "situation_actuelle",
        "contraintes",
        "echeancier",
        "fondement",
        "secteurId",
        "ministereId",
        "categorieId",
        "responsableId",
        "demandeurId",
        "titre_projet"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'statut'     => StatutIdee::class,
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
        });
    }

    public function champs()
    {
        return $this->morphToMany(Champ::class, 'projetable', 'champs_projet')
            ->using(ChampProjet::class)
            ->withPivot(['valeur', 'commentaire', 'id'])
            ->withTimestamps();
    }

    public function fiche_synthese()
    {
        return $this->morphToMany(Champ::class, 'projetable', 'champs_projet')
            ->using(ChampProjet::class)
            ->withPivot(['valeur', 'commentaire', 'id'])
            ->withTimestamps();
    }
}