<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commentaire extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'commentaires';

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
        'commentaire',
        'date',
        'commentaireable_type',
        'commentaireable_id',
        'commentaire_id',
        'commentateurId'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at'
    ];

    // Relations

    /**
     * Relation polymorphique vers l'entité commentée
     */
    public function commentaireable()
    {
        return $this->morphTo('commentaireable', 'commentaireable_type', 'commentaireable_id');
    }

    /**
     * Relation avec l'utilisateur qui a fait le commentaire
     */
    public function commentateur()
    {
        return $this->belongsTo(User::class, 'commentateurId');
    }

    // Scopes

    /**
     * Scope pour les commentaires récents
     */
    public function scopeRecents($query, int $jours = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($jours));
    }

    /**
     * Scope pour les commentaires d'un utilisateur
     */
    public function scopeParCommentateur($query, int $commentateurId)
    {
        return $query->where('commentateurId', $commentateurId);
    }

    /** Commentaire parent (nullable) */
    public function parent()
    {
        return $this->belongsTo(Commentaire::class, 'commentaire_id');
    }

    /** Réponses / sous-commentaires */
    public function enfants()
    {
        return $this->hasMany(Commentaire::class, 'commentaire_id');
    }

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        // Définir automatiquement la date lors de la création
        static::creating(function ($commentaire) {
            if (!$commentaire->date) {
                $commentaire->date = now();
            }
        });
    }

    /*
    *
    * Mapping "type en minuscule" => Classe du modèle
    */
    public static function getCommentaireableMap(): array
    {
        return [
            'fichier' => \App\Models\Fichier::class,
            'idee_de_projet' => \App\Models\IdeeProjet::class,
            'projet' => \App\Models\Projet::class,
            'note' => \App\Models\NoteConceptuelle::class,
            'tdr' => \App\Models\Tdr::class,
            'rapport' => \App\Models\Rapport::class,
            'evaluation' => \App\Models\Evaluation::class,
            'decision' => \App\Models\Decision::class,
            'champ_projet' => \App\Models\ChampProjet::class,
            'evaluation_critere' => \App\Models\EvaluationCritere::class
        ];
    }

    public static array $resourceArticles = [
        'projet' => 'le',
        'note' => 'la',
        'tdr' => 'le',
        'rapport' => 'le',
        'idee_de_projet' => "l'",
        'fichier' => 'le',
        'evaluation' => "l'",
        'decision' => 'la',
        'champ_projet' => 'le',
        'evaluation_critere' => "l'",
    ];


    /**
     * Mutator pour convertir le type en classe
     */
    public function setCommentaireableTypeAttribute($value)
    {
        if (!class_exists($value)) {
            $map = self::getCommentaireableMap();
            $this->attributes['commentaireable_type'] = $map[strtolower($value)] ?? $value;
        } else {
            $this->attributes['commentaireable_type'] = $value;
        }
    }
}
