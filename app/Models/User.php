<?php

namespace App\Models;

use App\Enums\EnumTypeOrganisation;
use App\Services\Traits\HasPermissionTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Contracts\OAuthenticatable;
use Laravel\Passport\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
/*
use Laravel\Sanctum\HasApiTokens;
*/
use Illuminate\Support\Str;

class User extends Authenticatable implements OAuthenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasPermissionTrait, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $default = ['provider' => 'keycloack'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'provider',
        'provider_user_id',
        'username',
        'email',
        'status',
        'is_email_verified',
        'email_verified_at',
        'password',
        'personneId',
        'roleId',
        'last_connection',
        'ip_address',
        'settings',
        'person',
        'keycloak_id',
        'type',
        'profilable_id',
        'profilable_type',
        'account_verification_request_sent_at',
        'password_update_at',
        'last_password_remember',
        'token',
        'link_is_valide',
        'lastRequest'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_email_verified' => 'boolean',
        'email_verified_at' => 'timestamp',
        'last_connection' => 'timestamp',
        'settings' => 'array',
        'person' => 'array',
        'created_at' => 'datetime:Y-m-d',
        'updated_at' => 'datetime:Y-m-d H:i:s',
        'deleted_at' => 'datetime:Y-m-d H:i:s',
        'link_is_valide' => 'boolean',
        'account_verification_request_sent_at' => 'timestamp',
        'password_update_at' => 'timestamp',
        'lastRequest' => 'datetime',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'updated_at',
        'deleted_at',
        'settings' => 'array',
        'person' => 'array',
    ];

    /**
     * Get the personne that owns the user.
     */
    public function personne()
    {
        return $this->belongsTo(Personne::class, 'personneId');
    }

    /**
     * Get the role that owns the user.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'roleId');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'userId', 'roleId');
    }

    public function permissions()
    {
        return Permission::where(function ($q) {
            // permissions du rôle
            $q->whereHas('roles', function ($qr) {
                $qr->where('roles.id', $this->roleId);
            });

            // permissions des groupes
            $q->orWhereHas('groupesUtilisateur', function ($qg) {
                $qg->whereIn('groupes_utilisateurs.id', $this->groupesUtilisateur()->pluck('groupes_utilisateur.id'));
            });
        });

        return $this->role->permissions();
        return $this->roles()->get()->last()->permissions();
    }

    /**
     * Get the user's organisation through personne.
     */
    public function organisation()
    {
        return $this->hasOneThrough(Organisation::class, Personne::class, 'id', 'id', 'personneId', 'organismeId');
    }

    /**
     * The model's boot method.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($model) {
            $model->update([
                'username' => time() . '::' . $model->username,
                'email' => time() . '::' . $model->email,
            ]);
        });
    }

    /**
     * Find the user instance for the given username.
     */
    public function findForPassport(string $username): User
    {
        return $this->where('email', $username)->first();
    }

    /**
     * Validate the password of the user for the Passport password grant.
     */
    public function validateForPassportPasswordGrant(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    /**
     * Get the user's ministry through personne (excludes super admin, DGPD)
     */
    public function ministere()
    {
        if (in_array($this->role->slug, ['super_admin', 'super-admin', 'dgpd'])) {
            return null;
        }

        return $this->personne ? $this->personne->ministere() : null;
    }

    public function getMinistereAttribute()
    {
        return $this->personne->ministere()->first();
    }


    /**
     * Create a new personal access token for the user.
     *
     * @param  string  $name
     * @param  array  $abilities
     * @return \Laravel\Sanctum\NewAccessToken
     */
    public function createUnToken(string $name, array $abilities = ['*'])
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(256)),
            'abilities' => $abilities,
        ]);

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    /**
     * Get the profile entity (polymorphic relation).
     */
    public function profilable()
    {
        return $this->morphTo();
    }

    /**
     * Get the user groups.
     */
    public function groupesUtilisateur()
    {/*
        return $this->belongsToMany(GroupeUtilisateur::class, 'groupe_utilisateur_users', 'userId', 'groupeUtilisateurId')
            ->withTimestamps()
            ->withPivot('deleted_at'); */

        return $this->belongsToMany(GroupeUtilisateur::class, 'groupe_utilisateur_users', 'userId', 'groupeUtilisateurId')
                    ->withTimestamps()
                    ->withPivot('deleted_at');
    }

    /**
     * Get evaluations where this user is the main evaluateur.
     */
    public function evaluationsAsEvaluateur()
    {
        return $this->hasMany(Evaluation::class, 'evaluateur_id');
    }

    /**
     * Get evaluations where this user validated.
     */
    public function evaluationsAsValidator()
    {
        return $this->hasMany(Evaluation::class, 'valider_par');
    }

    /**
     * Get all evaluation criteres this user has evaluated.
     */
    public function evaluationCriteres()
    {
        return $this->hasMany(EvaluationCritere::class, 'evaluateur_id');
    }

    /**
     * Get evaluations this user participated in through evaluation_criteres.
     */
    public function evaluationsParticipated()
    {
        return $this->belongsToMany(Evaluation::class, 'evaluation_criteres', 'evaluateur_id', 'evaluation_id')
            ->withPivot('critere_id', 'note', 'notation_id', 'categorie_critere_id')
            ->withTimestamps()
            ->distinct();
    }

    /**
     * Scope pour filtrer les utilisateurs rattachés à une organisation
     * descendante d’un ministère donné.
     *
     * Ce scope utilise une relation avec le modèle `Personne`, puis applique
     * à celui-ci le scope `ministeres`, qui filtre en fonction des descendants
     * du ministère identifié par son ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $idMinistere ID du ministère racine
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMinisteres(Builder $query, $idMinistere)
    {
        return $query->whereHas("personne", function ($query) use ($idMinistere) {
            $query->ministeres($idMinistere);
        });
    }

    /**
     * Scope pour vérifier si un user appartient à un ministère donné.
     *
     * Vérifie si le user a un profilable de type Organisation ou Dpaf
     * qui appartient au ministère spécifié.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $ministereId ID du ministère
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByMinistere(Builder $query, $ministereId)
    {
        // Récupérer tous les IDs d'organisations qui appartiennent au ministère
        $organisationIds = collect();

        // Pour les DPAF : directement via id_ministere
        $dpafIds = Dpaf::where('id_ministere', $ministereId)->pluck('id');

        // Pour les Organisations : utiliser la logique de la méthode ministere()
        $organisations = Organisation::all();
        foreach ($organisations as $organisation) {
            // Si c'est le ministère lui-même
            if ($organisation->type === EnumTypeOrganisation::MINISTERE && $organisation->id == $ministereId) {
                $organisationIds->push($organisation->id);
                continue;
            }

            // Recherche en profondeur dans la hiérarchie
            $current = $organisation;
            while ($current && $current->parent) {
                $current = $current->parent;
                if ($current->type === EnumTypeOrganisation::MINISTERE && $current->id == $ministereId) {
                    $organisationIds->push($organisation->id);
                    break;
                }
            }
        }

        return $query->whereIn("profilable_type", [Organisation::class, Dpaf::class])
            ->where("status", "actif")
            ->where(function($userQuery) use ($organisationIds, $dpafIds) {
                $userQuery->where(function($q) use ($dpafIds) {
                    // Users avec profilable de type Dpaf
                    $q->where('profilable_type', Dpaf::class)
                      ->whereIn('profilable_id', $dpafIds);
                })
                ->orWhere(function($q) use ($organisationIds) {
                    // Users avec profilable de type Organisation
                    $q->where('profilable_type', Organisation::class)
                      ->whereIn('profilable_id', $organisationIds);
                });
            });
    }

    /**
     * Scope pour filtrer les users qui ont une permission spécifique.
     * Utilise le trait HasPermissionTrait pour vérifier toutes les sources de permissions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $permission Slug de la permission
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithPermission(Builder $query, $permission)
    {
        return $query->where(function($subQuery) use ($permission) {
            // Via role direct (roleId)
            $subQuery->whereHas('role.permissions', function($permQuery) use ($permission) {
                $permQuery->where('slug', $permission);
            })/*
            // Via roles multiples (user_roles)
            ->orWhereHas('roles.permissions', function($permQuery) use ($permission) {
                $permQuery->where('slug', $permission);
            }) */
            // Via groupes utilisateur
            ->orWhereHas('groupesUtilisateur.permissions', function($permQuery) use ($permission) {
                $permQuery->where('slug', $permission);
            });
        });
    }
}
