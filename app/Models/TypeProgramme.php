<?php

namespace App\Models;

use App\Traits\HashableId;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class TypeProgramme extends Model
{
    use HasFactory, SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'types_programme';

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
        'type_programme', 'slug', 'typeId'
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
        'typeId', 'updated_at', 'deleted_at'
    ];

    /**
     * Get the parent type programme.
     */
    public function parent()
    {
        return $this->belongsTo(TypeProgramme::class, 'typeId');
    }

    /**
     * Get the child types programme.
     */
    public function children()
    {
        return $this->hasMany(TypeProgramme::class, 'typeId');
    }

    /**
     * Get the composants programme for the type programme.
     */
    public function composantsProgramme()
    {
        return $this->hasMany(ComposantProgramme::class, 'typeId');
    }

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->update([
                'type_programme' => time() . '::' . $model->type_programme,
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
    public function setTypeProgrammeAttribute($value)
    {
        $this->attributes['type_programme'] = Str::ucfirst(trim($value)); // Escape value with backslashes

        if(!isset($this->attributes['slug'])){
            $this->attributes['slug'] = $this->attributes['type_programme'];
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
        $this->attributes['slug'] = $value ?? Str::slug($this->attributes['type_programme']);
    }
    /**
    *
    * @param  string  $value
    * @return string
    */
    public function getTypeProgrammeAttribute($value){
        return ucfirst(str_replace('\\',' ',$value));
    }
}
