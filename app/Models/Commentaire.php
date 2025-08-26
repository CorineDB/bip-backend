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
}