<?php

namespace App\Models;

use App\Traits\HashableId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasResourcePermissions;

class Dossier extends Model
{
    use SoftDeletes, HashableId, HasResourcePermissions;

    protected $table = 'dossiers';

    protected $fillable = [
        'nom',
        'description',
        'parent_id',
        'created_by',
        'path', // chemin complet /dossier1/sousdossier
        'is_public',
        'couleur', // pour l'UI
        'icone',
        'profondeur'
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'profondeur' => 'integer'
    ];

    // Événements du modèle pour gérer automatiquement la profondeur et le path
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($dossier) {
            $dossier->calculateDepthAndPath();
        });

        static::updating(function ($dossier) {
            if ($dossier->isDirty('parent_id')) {
                $dossier->calculateDepthAndPath();
                $dossier->updateChildrenDepthAndPath();
            }
        });
    }

    // Relations
    public function parent()
    {
        return $this->belongsTo(Dossier::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Dossier::class, 'parent_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function fichiers()
    {
        return $this->hasMany(Fichier::class);
    }

    public function permissions()
    {
        //return $this->hasMany(DossierPermission::class);
    }

    // Méthodes utiles
    public function getFullPathAttribute()
    {
        if ($this->parent) {
            return $this->parent->full_path . '/' . $this->nom;
        }
        return $this->nom;
    }

    public function getAllChildren()
    {
        $children = collect();
        foreach ($this->children as $child) {
            $children->push($child);
            $children = $children->merge($child->getAllChildren());
        }
        return $children;
    }

    // Calculer automatiquement la profondeur et le chemin
    public function calculateDepthAndPath()
    {
        if ($this->parent_id) {
            $parent = static::find($this->parent_id);
            if ($parent) {
                $this->profondeur = $parent->profondeur + 1;
                $this->path = $parent->path . '/' . $this->nom;
            }
        } else {
            $this->profondeur = 0;
            $this->path = $this->nom;
        }
    }

    // Mettre à jour la profondeur et le chemin de tous les enfants
    public function updateChildrenDepthAndPath()
    {
        $children = $this->children;
        foreach ($children as $child) {
            $child->calculateDepthAndPath();
            $child->save();
            $child->updateChildrenDepthAndPath();
        }
    }

    // Scope pour limiter la profondeur maximum
    public function scopeMaxDepth($query, $maxDepth = 10)
    {
        return $query->where('profondeur', '<=', $maxDepth);
    }

    // Obtenir le fil d'Ariane jusqu'à la racine
    public function getBreadcrumb()
    {
        $breadcrumb = collect();
        $current = $this;

        while ($current) {
            $breadcrumb->prepend([
                'id' => $current->id,
                'nom' => $current->nom,
                'profondeur' => $current->profondeur
            ]);
            $current = $current->parent;
        }

        return $breadcrumb;
    }

    // Vérifier si on peut créer un sous-dossier (limite de profondeur)
    public function canCreateSubfolder($maxDepth = 10)
    {
        return $this->profondeur < $maxDepth;
    }
}
