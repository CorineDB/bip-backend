<?php

namespace App\Models;

use App\Helpers\SlugHelper;
use App\Traits\HashableId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class ChampSection extends Model
{
    use HasFactory, SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'champs_sections';

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
        'intitule',
        'description',
        'slug',
        'ordre_affichage',
        'type',
        'documentId',
        'parentSectionId'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
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
        'documentId', 'parentSectionId', 'updated_at', 'deleted_at'
    ];

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        // Pas besoin de calculer automatiquement le niveau - on utilise ordre_affichage

        static::deleting(function ($model) {
            $model->update([
                // Exemple : 'nom' => time() . '::' . $model->nom,
            ]);

            if (method_exists($model, 'user')) {
                // Exemple : $model->user()->delete();
            }
        });
    }

    /**
     * Calculer le niveau de profondeur de la section.
     */
    public function getNiveau()
    {
        if (is_null($this->parentSectionId)) {
            return 1; // Section racine
        }

        $parent = $this->parentSection;
        return $parent ? $parent->getNiveau() + 1 : 1;
    }

    /**
     * Obtenir le chemin hiérarchique complet.
     */
    public function getCheminHierarchique($separator = '/')
    {
        if (is_null($this->parentSectionId)) {
            return $this->slug; // Section racine
        }

        $parent = $this->parentSection;
        return $parent ? $parent->getCheminHierarchique($separator) . $separator . $this->slug : $this->slug;
    }

    /**
     * Get the document that owns the section.
     */
    public function document()
    {
        return $this->belongsTo(Document::class, 'documentId');
    }

    /**
     * Get all champs for this section.
     */
    public function champs()
    {
        return $this->hasMany(Champ::class, 'sectionId')->orderBy('ordre_affichage', 'asc');
    }

    /**
     * Get the parent section.
     */
    public function parentSection()
    {
        return $this->belongsTo(ChampSection::class, 'parentSectionId');
    }

    /**
     * Get the child sections (sous-sections).
     */
    public function childSections()
    {
        return $this->hasMany(ChampSection::class, 'parentSectionId')->orderBy('ordre_affichage', 'asc');
    }

    /**
     * Get all descendants (toutes les sous-sections à tous les niveaux).
     */
    public function descendants()
    {
        return $this->childSections()->with('descendants');
    }

    /**
     * Get all ancestors (toutes les sections parents jusqu'à la racine).
     */
    public function ancestors()
    {
        return $this->parentSection() ?
            collect([$this->parentSection])->merge($this->parentSection->ancestors()) :
            collect();
    }

    /**
     * Scope pour récupérer seulement les sections racines (niveau 1).
     */
    public function scopeRacines($query)
    {
        return $query->whereNull('parentSectionId');
    }

    /**
     * Scope pour récupérer les sections d'un niveau spécifique.
     */
    public function scopeNiveau($query, $niveau)
    {
        // Implémentation basée sur la profondeur de parentSectionId
        if ($niveau == 1) {
            return $query->whereNull('parentSectionId');
        }
        // Pour les niveaux > 1, on devrait faire une requête récursive plus complexe
        // Pour l'instant, on retourne toutes les sections avec un parent
        return $query->whereNotNull('parentSectionId');
    }

    /**
     * Vérifier si c'est une section racine.
     */
    public function estRacine()
    {
        return is_null($this->parentSectionId);
    }

    /**
     * Vérifier si la section a des enfants.
     */
    public function aDesEnfants()
    {
        return $this->childSections()->exists();
    }

    /**
     *
     *
     * @param  string  $value
     * @return void
     */
    public function setIntituleAttribute($value)
    {
        $this->attributes['intitule'] = Str::ucfirst(trim($value));

        if(!isset($this->attributes['slug'])){
            $this->attributes['slug'] = $this->attributes['intitule'];
        }
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $this->generateUniqueSlug($value ?? Str::slug($this->attributes['intitule']));
    }

    private function generateUniqueSlug($name)
    {
        return SlugHelper::generateUnique($name, static::class, 'slug', $this->id ?? null);
    }

    /**
     * Construire la liste ordonnée des éléments (champs et sous-sections mélangés) de manière récursive
     */
    public function getOrderedElements()
    {
        $elements = collect();

        // Ajouter les champs de cette section
        foreach ($this->champs->sortBy('ordre_affichage') as $champ) {
            $elements->push([
                'type' => 'champ',
                'element_type' => 'field',
                'ordre_affichage' => $champ->ordre_affichage,
                'data' => $champ
            ]);
        }

        // Ajouter les sous-sections
        foreach ($this->childSections->sortBy('ordre_affichage') as $sousSection) {
            $elements->push([
                'type' => 'section',
                'element_type' => 'section',
                'ordre_affichage' => $sousSection->ordre_affichage,
                'data' => $sousSection
            ]);
        }

        // Trier tous les éléments par ordre d'affichage
        return $elements->sortBy('ordre_affichage');
    }

    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getIntituleAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }
}
