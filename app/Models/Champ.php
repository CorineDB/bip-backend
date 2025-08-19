<?php

namespace App\Models;

use App\Enums\EnumTypeChamp;
use App\Helpers\SlugHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Champ extends Model
{
    use HasFactory, SoftDeletes/*, HasSecureIds*/;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'champs';

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
        'label',
        'info',
        'attribut',
        'placeholder',
        'is_required',
        'default_value',
        'isEvaluated',
        'ordre_affichage',
        'type_champ',
        'sectionId',
        'documentId',
        'meta_options'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_required' => 'boolean',
        'isEvaluated' => 'boolean',
        'type_champ' => EnumTypeChamp::class,
        'meta_options' => 'array',
        //'default_value' => 'array',
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
        'sectionId', 'documentId', 'updated_at', 'deleted_at'
    ];

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

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
     * Get the section that owns the champ.
     */
    public function section()
    {
        return $this->belongsTo(ChampSection::class, "sectionId");
    }

    /**
     * Get the document that owns the champ.
     */
    public function document()
    {
        return $this->belongsTo(Document::class, "documentId");
    }

    public function ideesDeProjet()
    {
        return $this->morphedByMany(IdeeProjet::class, "projetable", "champs_projet")
            ->using(ChampProjet::class)
            ->withPivot(["valeur", "commentaire", "id"])
            ->withTimestamps();
    }

    /**
     * Get all evaluations for this champ.
     */
    public function evaluations()
    {
        return $this->belongsToMany(Evaluation::class, "evaluation_champs", "champId", "evaluationId")
                    ->withPivot("note", "date_note", "commentaires")
                    ->withTimestamps();
    }

    /**
     *
     *
     * @param  string  $value
     * @return void
     */
    public function setLabelAttribute($value)
    {
        $this->attributes["label"] = Str::ucfirst(trim($value));
    }

    /**
     * Set the default_value attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setDefaultValueAttribute($value)
    {
        $this->attributes["default_value"] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Get the default_value attribute.
     *
     * @param  string|null  $value
     * @return mixed
     */
    public function getDefaultValueAttribute($value)
    {
        if (is_null($value)) {
            return null;
        }

        $decoded = json_decode($value, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }
}