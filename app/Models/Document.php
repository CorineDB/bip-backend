<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Document extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'documents';

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
        'nom',
        'slug',
        'description',
        'categorieId',
        'type',
        'metadata',
        'structure',
        'evaluation_configs'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'structure' => 'array',
        'evaluation_configs' => 'array',
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
        'categorieId', 'updated_at', 'deleted_at'
    ];

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->update([
                'nom' => time() . '::' . $model->nom,
                'slug' => time() . '::' . $model->slug,
            ]);

            if (method_exists($model, 'user')) {
                // Exemple : $model->user()->delete();
            }
        });

        /*static::updating(function ($model) {
            un
            $model->update([
                'nom' => time() . '::' . $model->nom,
                'slug' => time() . '::' . $model->slug,
            ]);

            if (method_exists($model, 'user')) {
                // Exemple : $model->user()->delete();
            }
        });*/
    }

    /**
     *
     *
     * @param  string  $value
     * @return void
     */
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = Str::ucfirst(trim($value)); // Escape value with backslashes

        if(!isset($this->attributes['slug'])){
            $this->attributes['slug'] = $this->attributes['nom'];
        }
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $this->generateUniqueSlug($value ?? Str::slug($this->attributes['nom']));
    }

    private function generateUniqueSlug($name)
    {
        $baseSlug = str_replace(' ', '-', strtolower($name));
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getNomAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }

    /**
     * Get the category that owns the document.
     */
    public function categorie()
    {
        return $this->belongsTo(CategorieDocument::class, 'categorieId');
    }

    /**
     * Get all sections for this document.
     */
    public function sections()
    {
        return $this->hasMany(ChampSection::class, 'documentId')->orderBy('ordre_affichage', 'asc');
    }

    /**
     * Get all champs for this document.
     */
    public function champs()
    {
        return $this->hasMany(Champ::class, 'documentId')->whereNull('sectionId')->orderBy('ordre_affichage', 'asc');
    }

    /**
     * Get all champs for this document.
     */
    public function all_champs()
    {
        return $this->hasMany(Champ::class, 'documentId');;
    }

    /**
     * Get all sections for this document.
     */
    public function all_sections()
    {
        return $this->hasMany(ChampSection::class, 'documentId');
    }

    /**
     * Get all fichiers attached to this document.
     */
    public function fichiers()
    {
        return $this->morphMany(Fichier::class, 'fichierAttachable', 'fichier_attachable_type', 'fichier_attachable_id')
                    ->ordered();
    }

    /**
     * Get fichiers by category.
     */
    public function fichiersByCategorie($categorie)
    {
        return $this->fichiers()->byCategorie($categorie);
    }

    /**
     * Get active fichiers only.
     */
    public function fichiersActifs()
    {
        return $this->fichiers()->active();
    }

    /**
     * Construire la liste ordonnée des éléments (champs et sections mélangés)
     */
    public function getOrderedElements()
    {
        $elements = collect();

        // Ajouter les champs globaux
        foreach ($this->champs->sortBy('ordre_affichage') as $champ) {
            $elements->push([
                'type' => 'champ',
                'element_type' => 'field',
                'ordre_affichage' => $champ->ordre_affichage,
                'data' => $champ
            ]);
        }

        // Ajouter les sections parents
        foreach ($this->sections->whereNull('parentSectionId')->sortBy('ordre_affichage') as $section) {
            $elements->push([
                'type' => 'section',
                'element_type' => 'section',
                'ordre_affichage' => $section->ordre_affichage,
                'data' => $section
            ]);
        }

        // Trier tous les éléments par ordre d'affichage
        return $elements->sortBy('ordre_affichage');
    }
}