<?php

namespace App\Models;

use App\Traits\HashableId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Cible extends Model
{
    use HasFactory, SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cibles';

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
        'cible', 'slug', 'oddId'
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
        'updated_at', 'deleted_at'
    ];

    /**
     * Get the projets that belong to the cible through cibles_projets pivot table.
     */
    public function projets()
    {
        return $this->morphedByMany(
            'App\Models\Projet',
            'projetable',
            'cibles_projets',
            'cibleId'
        );
    }

    /**
     * Get the idees projets that belong to the cible through cibles_projets pivot table.
     */
    public function ideesProjet()
    {
        return $this->morphedByMany(
            'App\Models\IdeeProjet',
            'projetable',
            'cibles_projets',
            'cibleId'
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
                'cible' => time() . '::' . $model->cible,
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
    public function setCibleAttribute($value)
    {
        $this->attributes['cible'] = Str::ucfirst(trim($value));

        if(!isset($this->attributes['slug'])){
            $this->attributes['slug'] = $this->attributes['cible'];
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
        $this->attributes['slug'] = $value ?? Str::slug($this->attributes['cible']);
    }

    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getCibleAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }
}
