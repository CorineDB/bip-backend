<?php

namespace App\Models;

use App\Enums\EnumTypeSecteur;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Secteur extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'secteurs';

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
        'type',
        'secteurId'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => EnumTypeSecteur::class,
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
        'secteurId', 'updated_at', 'deleted_at'
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
    }

    /**
     * Get the parent secteur (self-referencing).
     */
    public function parent()
    {
        return $this->belongsTo(Secteur::class, 'secteurId');
    }

    /**
     * Get the child secteurs.
     */
    public function children()
    {
        return $this->hasMany(Secteur::class, 'secteurId');
    }

    /**
     * Get all types d'intervention for this secteur.
     */
    public function typesIntervention()
    {
        return $this->hasMany(TypeIntervention::class, 'secteurId');
    }

    /**
     * Récupérer le secteur principal en remontant la hiérarchie
     */
    public function getSecteurPrincipal()
    {
        $secteurCourant = $this;
        
        // Si c'est déjà un secteur principal, le retourner
        if ($secteurCourant->type->value === 'secteur') {
            return $secteurCourant;
        }
        
        // Remonter dans la hiérarchie jusqu'à trouver un secteur de type 'secteur'
        while ($secteurCourant && $secteurCourant->type->value === 'sous-secteur') {
            $secteurCourant = $secteurCourant->parent;
        }
        
        // Retourner le secteur principal trouvé ou null
        return ($secteurCourant && $secteurCourant->type->value === 'secteur') ? $secteurCourant : null;
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
     *
     * @param  string  $value
     * @return void
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $this->generateUniqueSlug($value ?? Str::slug($this->attributes['nom']));
    }

    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getNomAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }
}