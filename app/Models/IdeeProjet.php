<?php

namespace App\Models;

use App\Enums\PhasesIdee;
use App\Enums\SousPhaseIdee;
use App\Enums\StatutIdee;
use App\Enums\TypesProjet;
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
    protected $table = 'idees_projet';

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
        "est_soumise",
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
        'score_pertinence',
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
        "demandeur_type",
        'porteur_projet',
        "titre_projet",
        'canevas_appreciation_pertinence',
        'canevas_climatique',
        'canevas_amc'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'statut'     => StatutIdee::class,
        'phase'     => PhasesIdee::class,
        'sous_phase'     => SousPhaseIdee::class,
        'type_projet'     => TypesProjet::class,
        // Seules les vraies colonnes JSON selon la migration
        'decision' => 'array',
        'cout_estimatif_projet' => 'array',
        'ficheIdee' => 'array',
        'parties_prenantes' => 'array',
        'objectifs_specifiques' => 'array',
        'resultats_attendus' => 'array',
        'body_projet' => 'array',
        'canevas_appreciation_pertinence' => 'array',
        'canevas_climatique' => 'array',
        'canevas_amc' => 'array',
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
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $default = [
        "type_projet"   => TypesProjet::simple,
        "phase"         => PhasesIdee::identification,
        "sous_phase"    => SousPhaseIdee::redaction,
        "statut"        => StatutIdee::BROUILLON,
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

        static::saving(function ($model) {
            // Nettoyer les valeurs JSON vides avant la sauvegarde
            $model->cleanJsonAttributes();
        });
    }

    /**
     * Nettoyer les attributs JSON pour éviter les erreurs PostgreSQL
     */
    protected function cleanJsonAttributes(): void
    {
        $jsonColumns = [
            'decision', 'cout_estimatif_projet', 'ficheIdee',
            'parties_prenantes', 'objectifs_specifiques',
            'resultats_attendus', 'body_projet', 'canevas_appreciation_pertinence',
            'canevas_climatique', 'canevas_amc'
        ];

        foreach ($jsonColumns as $column) {
            if (!isset($this->attributes[$column])) {
                continue;
            }

            $value = $this->attributes[$column];

            // Si la valeur est une chaîne vide, la convertir en null ou array vide
            if ($value === '' || $value === null) {
                // Pour les colonnes obligatoires, utiliser un array vide
                if (in_array($column, ['ficheIdee', 'body_projet'])) {
                    $this->attributes[$column] = '[]';
                } else {
                    $this->attributes[$column] = null;
                }
            }
            // Si c'est déjà un array, l'encoder en JSON
            elseif (is_array($value)) {
                $this->attributes[$column] = json_encode($value);
            }
            // Si c'est une chaîne non vide qui n'est pas du JSON valide
            elseif (is_string($value) && !$this->isValidJson($value)) {
                // Tenter de convertir en array si ce n'est pas du JSON
                if (trim($value) !== '') {
                    $this->attributes[$column] = json_encode([$value]);
                } else {
                    $this->attributes[$column] = null;
                }
            }
            // Si c'est déjà du JSON valide, le laisser tel quel
        }
    }

    /**
     * Vérifier si une chaîne est du JSON valide
     */
    private function isValidJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    public function champs()
    {
        return $this->morphToMany(Champ::class, 'projetable', 'champs_projet', 'projetable_id', 'champId')
            ->using(ChampProjet::class)
            ->withPivot(['valeur', 'commentaire', 'id'])
            ->withTimestamps();
    }

    public function fiche_synthese()
    {
        return $this->morphToMany(Champ::class, 'projetable', 'champs_projet', 'projetable_id', 'champId')
            ->using(ChampProjet::class)
            ->withPivot(['valeur', 'commentaire', 'id'])
            ->withTimestamps();
    }

    /**
     * Get the projetable entity that the evaluation belongs to.
     */
    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentaireable');
    }

    /**
     * Get the projetable entity that the evaluation belongs to.
     */
    public function decisions()
    {
        return $this->morphMany(Decision::class, 'objet_decision');
    }

    /**
     * Get the projetable entity that the evaluation belongs to.
     */
    public function workflows()
    {
        return $this->morphMany(Workflow::class, 'projetable');

    }

    public function secteur()
    {
        return $this->belongsTo(Secteur::class, 'secteurId')->where('type', "sous-secteur")->whereHas('parent', function($query){
            $query->where('type', 'secteur');
        });
    }

    public function ministere()
    {
        return $this->belongsTo(Organisation::class, 'ministereId')->where('type', "ministere")->whereNull("parentId");
    }

    public function categorie()
    {
        return $this->belongsTo(CategorieProjet::class, 'categorieId');
    }

    public function projet()
    {
        return $this->hasOne(Projet::class, 'ideeProjetId');
    }

    public function responsable()
    {
        return $this->belongsTo(User::class, 'responsableId');
    }

    public function demandeur()
    {
        return $this->morphTo('demandeur', 'demandeur_type', 'demandeurId');
    }

    public function financements()
    {
        return $this->morphToMany(Financement::class, 'projetable', 'sources_financement_projets', 'projetable_id', 'sourceId')
            ->withTimestamps();
    }

    public function sources_de_financement()
    {
        return $this->morphToMany(Financement::class, 'projetable', 'sources_financement_projets', 'projetable_id', 'sourceId')->where("type", "source")
            ->withTimestamps();
    }

    public function cibles()
    {
        return $this->morphToMany(Cible::class, 'projetable', 'cibles_projets', 'projetable_id', 'cibleId')
            ->withTimestamps();
    }

    public function odds()
    {
        return $this->morphToMany(Odd::class, 'projetable', 'odds_projets', 'projetable_id', 'oddId')
            ->withTimestamps();
    }

    public function typesIntervention()
    {
        return $this->morphToMany(TypeIntervention::class, 'projetable', 'types_intervention_projets', 'projetable_id', 'typeId')
            ->withTimestamps();
    }

    public function lieuxIntervention()
    {
        return $this->morphMany(LieuIntervention::class, 'projetable');
    }

    public function composants()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')
            ->withTimestamps();
    }

    public function orientations_strategique_png()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'orientation-strategique-pnd');
        });
    }

    public function objectifs_strategique_png()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'objectif-strategique-pnd');
        });
    }
    public function resultats_strategique_png()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'resultats-strategique-pnd');
        });
    }

    public function axes_pag()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'axe-pag');
        });
    }

    public function actions_pag()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'action-pag');
        });
    }

    public function pilliers_pag()
    {
        return $this->morphToMany(ComposantProgramme::class, 'projetable', 'composants_projet', 'projetable_id', 'composantId')->whereHas('typeProgramme', function($query){
            $query->where('slug', 'pillier-pag');
        });
    }

    public function documents()
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    public function evaluations()
    {
        return $this->morphMany(Evaluation::class, 'projetable');
    }

    public function evaluationsClimatique()
    {
        return $this->morphMany(Evaluation::class, 'projetable')->where("type", "climatique");
    }

    public function evaluationsPertinence()
    {
        return $this->morphMany(Evaluation::class, 'projetable')->where("type", "pertinence");
    }

    public function evaluationPertinence()
    {
        /* return $this->morphOne(Evaluation::class, 'projetable')->where("type", "pertinence")
                ->orderBy('created_at', 'desc')
                ->first(); */
        return $this->morphMany(Evaluation::class, 'projetable')
            ->where('type_evaluation', 'pertinence')/*
            ->latestOfMany() */; // ✅ équivalent à orderBy('created_at', 'desc')->first()

    }

    public function evaluationsAMC()
    {
        return $this->morphMany(Evaluation::class, 'projetable')->where("type", "amc");
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichierAttachable', 'fichier_attachable_type', 'fichier_attachable_id')
            ->active()
            ->ordered();
    }

    public function allFichiers()
    {
        return $this->morphMany(Fichier::class, 'fichierAttachable', 'fichier_attachable_type', 'fichier_attachable_id');
    }

    public function fichiersParCategorie(string $categorie)
    {
        return $this->fichiers()->byCategorie($categorie);
    }

    public function scopeEvaluateursClimatique()
    {
        return User::byMinistere($this->ministere->id);
    }
}
