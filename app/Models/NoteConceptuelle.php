<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NoteConceptuelle extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notes_conceptuelle';

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
        'projetId',
        'intitule',
        'statut',
        'valider_par',
        'rediger_par',
        'note_conceptuelle',
        'decision',
        'numero_contrat',
        'numero_dossier',
        'accept_term',
        'canevas_redaction_note_conceptuelle',
        'canevas_appreciation_note_conceptuelle',
        'parentId'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'note_conceptuelle' => 'array',
        'decision' => 'array',
        'canevas_redaction_note_conceptuelle' => 'array',
        'canevas_appreciation_note_conceptuelle' => 'array',
        'statut' => 'integer',
        'date_validation' => 'datetime',
        'accept_term'               => 'boolean',
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
            $model->update([
                'intitule' => time() . '::' . $model->intitule,
            ]);
        });
    }

    /**
     * Relation avec le projet
     */
    public function projet()
    {
        return $this->belongsTo(Projet::class, 'projetId');
    }

    /**
     * Relation avec tous les TDRs du projet
     */
    public function historique_des_notes_conceptuelle()
    {
        /* return $this->hasMany(NoteConceptuelle::class, 'projetId', 'projetId')
                    ->where('id', '!=', $this->id)
                    ->orderBy('created_at', 'desc'); */
        return $this->projet->notes_conceptuelle()->where("id", "!=", $this->id)->orderBy("created_at", "desc");
    }

    /**
     * Relation avec tous les TDRs du projet
     */
    public function historique_des_evaluations_notes_conceptuelle()
    {
        return $this->historique_des_notes_conceptuelle()->with(["evaluations" => function($query){
            $query->evaluationTermine("note-conceptuelle")->first();
        }]);
    }

    /**
     * Relation avec le TDR parent (version précédente)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(NoteConceptuelle::class, 'parentId');
    }

    /**
     * Relation avec le validateur
     */
    public function validateur()
    {
        return $this->belongsTo(User::class, 'valider_par');
    }

    /**
     * Relation avec le rédacteur
     */
    public function redacteur()
    {
        return $this->belongsTo(User::class, 'rediger_par');
    }

    /**
     * Relation many-to-many avec les champs (pour les valeurs saisies)
     */
    public function champs()
    {
        return $this->morphToMany(Champ::class, 'projetable', 'champs_projet', 'projetable_id', 'champId')
            ->using(ChampProjet::class)
            ->withPivot(['valeur', 'commentaire', 'id'])
            ->withTimestamps();
    }

    public function evaluations()
    {
        return $this->morphMany(Evaluation::class, 'projetable')->where('type_evaluation', "note-conceptuelle");
    }

    public function evaluationTermine()
    {
        return $this->evaluations()->evaluationTermine("note-conceptuelle")->first();
    }

    public function evaluationEnCours()
    {
        return $this->evaluations()->evaluationsEnCours("note-conceptuelle")->first();
    }

    public function evaluationParent()
    {
        return $this->evaluations()->evaluationParent("note-conceptuelle")->where()->first();
    }

    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichierAttachable', 'fichier_attachable_type', 'fichier_attachable_id')
            ->active()
            ->ordered();
    }
}
