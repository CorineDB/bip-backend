<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Odd extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'odds';

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
        'odd', 'slug'
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
     * Get the projets that belong to the odd through odds_projets pivot table.
     */
    public function projets()
    {
        return $this->morphedByMany(
            'App\Models\Projet',
            'projetable',
            'odds_projets',
            'oddId'
        );
    }

    /**
     * Get the idees projets that belong to the odd through odds_projets pivot table.
     */
    public function ideesProjet()
    {
        return $this->morphedByMany(
            'App\Models\IdeeProjet',
            'projetable',
            'odds_projets',
            'oddId'
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
                'odd' => time() . '::' . $model->odd,
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
    public function setOddAttribute($value)
    {
        $this->attributes['odd'] = Str::ucfirst(trim($value)); // Escape value with backslashes

        if(!isset($this->attributes['slug'])){
            $this->attributes['slug'] = $this->attributes['odd'];
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
        $this->attributes['slug'] = $value ?? Str::slug($this->attributes['odd']);
    }

    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getOddAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }
}
