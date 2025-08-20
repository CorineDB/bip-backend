<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class Fichier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'fichiers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nom_original',
        'nom_stockage',
        'chemin',
        'extension',
        'mime_type',
        'taille',
        'hash_md5',
        'description',
        'metadata',
        'fichier_attachable_id',
        'fichier_attachable_type',
        'categorie',
        'ordre',
        'uploaded_by',
        'is_public',
        'is_active',
        "commentaire"
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'taille' => 'integer',
        'ordre' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    // Relations

    /**
     * Relation polymorphique vers l'entité attachée
     */
    public function fichierAttachable()
    {
        return $this->morphTo('fichierAttachable', 'fichier_attachable_type', 'fichier_attachable_id');
    }

    /**
     * Utilisateur qui a uploadé le fichier
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Accessors

    /**
     * Obtenir l'URL complète du fichier
     */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->chemin);
    }

    /**
     * Obtenir la taille formatée (KB, MB, GB)
     */
    public function getTailleFormateeAttribute(): string
    {
        $bytes = $this->taille;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Vérifier si le fichier est une image
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Vérifier si le fichier est un document
     */
    public function getIsDocumentAttribute(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    // Scopes

    /**
     * Scope pour les fichiers actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les fichiers publics
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope par catégorie
     */
    public function scopeByCategorie($query, string $categorie)
    {
        return $query->where('categorie', $categorie);
    }

    /**
     * Scope ordonné
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('ordre')->orderBy('created_at');
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();

        // Supprimer le fichier physique lors de la suppression du modèle
        static::deleting(function ($fichier) {
            if (Storage::exists($fichier->chemin)) {
                Storage::delete($fichier->chemin);
            }
        });
    }
}
