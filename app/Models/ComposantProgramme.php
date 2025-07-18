<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ComposantProgramme extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'composants_programme';

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
        'indice', 'intitule', 'slug', 'typeId'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'indice' => 'integer',
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
        'typeId', 'updated_at', 'deleted_at'
    ];

    /**
     * Get the type programme that owns the composant programme.
     */
    public function typeProgramme()
    {
        return $this->belongsTo(TypeProgramme::class, 'typeId');
    }

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->update([
                'intitule' => time() . '::' . $model->intitule,
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
    public function setIntituleAttribute($value)
    {
        $this->attributes['intitule'] = addslashes($value); // Escape value with backslashes
        $this->attributes['slug'] = str_replace(' ', '-', strtolower($value));
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
