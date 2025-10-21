<?php

namespace App\Models;

use App\Enums\EnumTypeFinancement;
use App\Traits\HashableId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Financement extends Model
{
    use HasFactory, SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'financements';

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
        'nom', 'nom_usuel', 'slug', 'type', 'financementId'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => EnumTypeFinancement::class,
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
        'financementId', 'updated_at', 'deleted_at'
    ];

    /**
     * Get the parent financement.
     */
    public function parent()
    {
        return $this->belongsTo(Financement::class, 'financementId');
    }

    /**
     * Get the child financements.
     */
    public function children()
    {
        return $this->hasMany(Financement::class, 'financementId');
    }

    /**
     * Get the projets that belong to the financement through sources_financement_projets pivot table.
     */
    public function projets()
    {
        return $this->morphedByMany(
            'App\Models\Projet',
            'projetable',
            'sources_financement_projets',
            'sourceId'
        );
    }

    /**
     * Get the idees projets that belong to the financement through sources_financement_projets pivot table.
     */
    public function ideesProjet()
    {
        return $this->morphedByMany(
            'App\Models\IdeeProjet',
            'projetable',
            'sources_financement_projets',
            'sourceId'
        );
    }

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
        });
    }

    /**
     *
     *
     * @param  string  $value
     * @return void
     */
    public function setNomAttribute($value)
    {
        $this->attributes['nom'] = addslashes($value); // Escape value with backslashes
        $this->attributes['slug'] = str_replace(' ', '-', strtolower($value));
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
