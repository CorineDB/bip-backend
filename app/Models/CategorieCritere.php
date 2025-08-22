<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class CategorieCritere extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'categories_critere';

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
        'type',
        'slug',
        'is_mandatory',
        'critere_paramatrable'
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
                'type' => time() . '::' . $model->type,
                'slug' => time() . '::' . $model->slug,
            ]);

            if (method_exists($model, 'user')) {
                // Exemple : $model->user()->delete();
            }
        });
    }
    /**
     *
     *
     * @param  string  $value
     * @return void
     */
    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = Str::ucfirst(trim($value)); // Escape value with backslashes

        if(!isset($this->attributes['slug'])){
            $this->attributes['slug'] = $this->attributes['type'];
        }
    }

    /**
     *
     *
     * @param  string  $value
     * @return void
     */
    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = $value ?? Str::slug($this->attributes['type']);
    }

    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getTypeAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }

    /**
     * Get the critere
     */
    public function criteres()
    {
        return $this->hasMany(Critere::class, 'categorie_critere_id');
    }

    /**
     * Get the notations
     */
    public function notations()
    {
        return $this->hasMany(Notation::class, 'categorie_critere_id')->whereNull("critere_id");
    }

    /**
     * Get all fichiers attached to this categorie critere.
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
     * Get documents referentiel (fichiers de catégorie 'referentiel')
     */
    public function documentsReferentiel()
    {
        return $this->fichiers()->byCategorie('referentiel');
    }
}