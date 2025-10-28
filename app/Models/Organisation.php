<?php

namespace App\Models;

use App\Traits\HashableId;
use App\Enums\EnumTypeOrganisation;
use App\Observers\OrganisationObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Organisation extends Model
{
    use HasFactory, SoftDeletes, HashableId;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'organisations';

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
        'parentId'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => EnumTypeOrganisation::class,
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
        'updated_at',
        'deleted_at'
    ];

    /**
     * Get the parent organisation.
     */
    public function parent()
    {
        return $this->belongsTo(Organisation::class, 'parentId');
    }

    /**
     * Get the child organisations.
     */
    public function children()
    {
        return $this->hasMany(Organisation::class, 'parentId');
    }

    /**
     * Get the personnes for the organisation.
     */
    public function admin()
    {
        return $this->hasOne(Personne::class, 'organismeId')->whereHas("user", function ($query) {
            $query->where("type", "organisation")->whereHas("role", function ($query) {
                $query->where("slug", "organisation");
            });
        });
    }

    /**
     * Get the personnes for the organisation.
     */
    public function personnes()
    {
        return $this->hasMany(Personne::class, 'organismeId');
    }

    /**
     * Get the personnes for the organisation.
     */
    public function users()
    {
        return $this->hasManyThrough(User::class, Personne::class, 'organismeId', 'personneId', 'id', 'id')
        ->where('status', 'actif');
    }

    /**
     * Get the organisation that owns the role (polymorphic).
     */
    public function roles()
    {
        return $this->morphMany(Role::class, 'roleable');
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

        // Enregistrement direct de l’observer ici 👇
        static::observe(OrganisationObserver::class);
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
    public function getNomAttribute($value)
    {
        return ucfirst(str_replace('\\', ' ', $value));
    }

    public function user()
    {
        return $this->morphOne(User::class, 'profilable');
    }

    public function dpaf()
    {
        return $this->hasOne(Dpaf::class, 'id_ministere')->where('type', 'ministere');
    }

    /**
     * Récupérer le ministère de tutelle de l'organisation.
     *
     * Remonte récursivement la hiérarchie des parents jusqu'à
     * trouver l'organisation dont le type est MINISTERE.
     *
     * Méthode qui simule la relation vers le ministère racine.
     * Retourne un Builder Eloquent.
     *
     * Usage :
     * - $organisation->ministere()->first()
     * - $organisation->ministere()->where(...)->first();
     * - $organisation->ministere()->exists();
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function ministere()
    {
        // Si l'organisation est déjà un ministère, on renvoie elle-même
        if ($this->type === EnumTypeOrganisation::MINISTERE) {
            return self::where('id', $this->id);
        }

        $current = $this;
        // Remonter la hiérarchie des parents
        while ($current->parent) {
            $current = $current->parent;
            if ($current->type === EnumTypeOrganisation::MINISTERE) {
                return self::where('id', $current->id);
            }
        }

        // Requête vide pour éviter erreur // Aucun ministère trouvé
        return self::whereRaw('1 = 0');
    }

    /**
     * Accessor pour accéder directement à l'instance du ministère racine.
     *
     * Usage : $organisation->ministere
     *
     * @return Organisation|null
     */
    public function getMinistereAttribute()
    {
        return $this->ministere()->first();
    }

    /**
     * Get the users that belong to this organisaiton (polymorphic).
     */
    public function membres()
    {
        return $this->morphMany(User::class, 'profilable');
    }

    /**
     * Scope to filter by ministeres.
     */
    public function scopeMinisteres($query)
    {
        return $query->where('type', 'ministere');
    }

    /**
     * Scope to filter by institutions.
     */
    public function scopeInstitutions($query, $idMinisteres = null)
    {
        return $query->where('type', 'etatique');
    }

    /**
     * Scope to filter by institutions.
     */
    public function scopeInstitutionsWithMinistere($query)
    {
        return $query->scopeInstitutions()->with("organisation", function ($query) {
            $query->where("type", "ministere");
        });
    }

    /**
     * Scope pour récupérer le ministre d'une organisation.
     *
     * Ce scope retourne l'organisation de type ministère qui est soit :
     * - l'organisation elle-même si elle est de type ministère
     * - l'organisation parente de type ministère en remontant la hiérarchie
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $organisationId ID de l'organisation
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMinistere($query, $organisationId)
    {
        $organisation = self::find($organisationId);

        if (!$organisation) {
            return $query->whereRaw('1 = 0');
        }

        if ($organisation->type === EnumTypeOrganisation::MINISTERE) {
            return $query->where('id', $organisation->id);
        }

        $current = $organisation;
        while ($current->parent) {
            $current = $current->parent;
            if ($current->type === EnumTypeOrganisation::MINISTERE) {
                return $query->where('id', $current->id);
            }
        }

        return $query->whereRaw('1 = 0');
    }

    /**
     * Scope pour récupérer toutes les organisations descendantes d'un ministère donné,
     * en excluant le ministère lui-même, à l'aide d'une requête récursive SQL (nécessite MySQL 8+ ou PostgreSQL).
     *
     * Ce scope utilise une CTE récursive (WITH RECURSIVE) pour parcourir la hiérarchie
     * des organisations à partir de l'ID du ministère racine. Il permet d'obtenir efficacement
     * tous les enfants directs et indirects sans recourir à une boucle en PHP.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $ministereId ID du ministère racine
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function scopeDescendantsFromMinistere($query, $ministereId)
    {
        $ministereId = (int) $ministereId;

        $descendants = DB::select("
            WITH RECURSIVE descendants AS (
                SELECT \"id\", \"parentId\"
                FROM \"organisations\"
                WHERE \"parentId\" = ?
                UNION ALL
                SELECT o.\"id\", o.\"parentId\"
                FROM \"organisations\" o
                INNER JOIN descendants d ON o.\"parentId\" = d.\"id\"
            )
            SELECT \"id\" FROM descendants
        ", [$ministereId]);

        $ids = collect($descendants)->pluck('id');

        return $query->whereIn('id', $ids);

        return $query->whereIn('id', function ($subQuery) use ($ministereId) {
            $subQuery->select('id')
                ->from(DB::raw("(
                        WITH RECURSIVE descendants AS (
                            SELECT id, parentId
                            FROM organisations
                            WHERE parentId = ?
                            UNION ALL
                            SELECT o.id, o.parentId
                            FROM organisations o
                            INNER JOIN descendants d ON o.parentId = d.id
                        )
                        SELECT id FROM descendants
                    ) AS descendants_subquery"), [$ministereId]);
        });

        $rows = DB::select("
        WITH RECURSIVE descendants AS (
            SELECT id, parentId
            FROM organisations
            WHERE parentId = :ministereId
            UNION ALL
            SELECT o.id, o.parentId
            FROM organisations o
            INNER JOIN descendants d ON o.parentId = d.id
        )
        SELECT id FROM descendants", ['ministereId' => $ministereId]);

        $ids = collect($rows)->pluck('id');

        return $query->whereIn('id', $ids);
    }
}
