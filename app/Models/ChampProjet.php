<?php

namespace App\Models;

use App\Traits\HashableId;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Relations\MorphPivot;

class ChampProjet extends MorphPivot
{
    use SoftDeletes, HashableId;
    protected $table = 'champs_projet';

    protected $fillable = [
        'champId',
        'valeur',
        'commentaire',
        'projetable_id',
        'projetable_type',
    ];

    protected $casts = [
        'valeur' => 'json',
    ];

    // 🔁 Relation vers Champ
    public function champ()
    {
        return $this->belongsTo(Champ::class, 'champId');
    }

    public function projetable(){
        return $this->morphTo();
    }

    public function commentaires()
    {
        return $this->morphMany(Commentaire::class, 'commentable');
    }

    // 💡 Formatteur de valeur
    public function getValeurFormateeAttribute()
    {
        $type = $this->champ?->type;

        return match ($type) {
            'texte' => (string) $this->valeur,
            'number', 'numeric', 'float', 'decimal' => (float) $this->valeur,
            'integer' => (int) $this->valeur,
            'boolean', 'bool' => (bool) $this->valeur,
            'json', 'object', 'array' => json_encode($this->valeur),
            default => $this->valeur,
        };
    }
}
