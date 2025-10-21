<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use App\Http\Resources\GroupeUtilisateurResource;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\Permission;
use App\Repositories\Contracts\GroupeUtilisateurRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Repositories\Contracts\PersonneRepositoryInterface;
use App\Services\Contracts\GroupeUtilisateurServiceInterface;
use App\Models\User;
use App\Models\Role;
use App\Traits\GenerateTemporaryPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class GroupeUtilisateurService extends BaseService implements GroupeUtilisateurServiceInterface
{
    use GenerateTemporaryPassword;

    protected BaseRepositoryInterface $repository;
    protected UserRepositoryInterface $userRepository;
    protected RoleRepositoryInterface $roleRepository;
    protected PersonneRepositoryInterface $personneRepository;

    public function __construct(
        GroupeUtilisateurRepositoryInterface $repository,
        UserRepositoryInterface $userRepository,
        RoleRepositoryInterface $roleRepository,
        PersonneRepositoryInterface $personneRepository
    ) {
        parent::__construct($repository);
        $this->userRepository = $userRepository;
        $this->roleRepository = $roleRepository;
        $this->personneRepository = $personneRepository;
    }


    protected function getResourceClass(): string
    {
        return GroupeUtilisateurResource::class;
    }

    public function all(): JsonResponse
    {
        try {

            $item = $this->repository->getModel()->where("profilable_id", Auth::user()->profilable_id)->where("profilable_type", Auth::user()->profilable_type)->get();

            return ($this->resourceClass::collection($item->load(['roles', "users"])))->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Create a new group of users.
     */
    public function create(array $data): JsonResponse
    {
        DB::beginTransaction();

        try {
            $data['profilable_id'] = Auth::user()->profilable_id;
            $data['profilable_type'] = Auth::user()->profilable_type;

            // Créer le groupe
            $groupe = $this->repository->create($data);

            // Permissions de base fournies
            $permissionsIds = $data['permissions'] ?? [];

            // Ajouter les permissions provenant des rôles
            if (!empty($data['roles']) && is_array($data['roles'])) {
                $existingRoles = Role::whereIn('id', $data['roles'])->get();
                if (count($existingRoles) === count($data['roles'])) {
                    foreach ($existingRoles as $role) {
                        $rolePermissions = $role->permissions->pluck('id')->toArray();
                        $permissionsIds = array_merge($permissionsIds, $rolePermissions);
                    }
                } else {
                    DB::rollBack();
                    return response()->json([
                        'statut' => 'error',
                        'message' => 'Certains rôles spécifiés n\'existent pas',
                        'data' => null,
                        'statutCode' => Response::HTTP_BAD_REQUEST
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            // Nettoyage des doublons avant attachement
            $permissionsIds = array_unique($permissionsIds);

            // Attacher les permissions au groupe
            if (!empty($permissionsIds)) {
                $groupe->permissions()->attach($permissionsIds);
            }

            // Ajouter / Créer les utilisateurs
            if (!empty($data['users']) && is_array($data['users'])) {
                foreach ($data['users'] as $userData) {
                    if (isset($userData['id'])) {
                        // Utilisateur existant
                        $user = User::find($userData['id']);
                        if (!$user) {
                            DB::rollBack();
                            return response()->json([
                                'statut' => 'error',
                                'message' => 'Utilisateur avec ID ' . $userData['id'] . ' introuvable',
                                'data' => null,
                                'statutCode' => Response::HTTP_BAD_REQUEST
                            ], Response::HTTP_BAD_REQUEST);
                        }
                    } else {
                        // Création d’un nouvel utilisateur
                        $user = User::create([
                            'email' => $userData['email'],
                            'password' => bcrypt(Str::random(12)), // mot de passe aléatoire
                            'profilable_id' => $data['profilable_id'],
                            'profilable_type' => $data['profilable_type'],
                        ]);

                        // Création de la personne liée
                        if (!empty($userData['personne'])) {
                            $user->personne()->create([
                                'nom' => $userData['personne']['nom'],
                                'prenom' => $userData['personne']['prenom'],
                                'poste' => $userData['personne']['poste'] ?? null,
                            ]);
                        }
                    }

                    // Attacher au groupe
                    $groupe->users()->attach($user->id);
                }
            }
            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a créé le groupe d'utilisateurs {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Groupe d\'utilisateurs créé avec succès',
                'data' => new GroupeUtilisateurResource($groupe->load(['roles', 'users', 'profilable'])),
                'statutCode' => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    /**
     * Update an existing group of users.
     */
    public function update(int|string $id, array $data): JsonResponse
    {
        DB::beginTransaction();

        try {
            $groupe = $this->repository->findById($id);

            if (!$groupe) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Groupe d\'utilisateurs non trouvé',
                    'data' => null,
                    'statutCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }

            unset($data["profilable_id"], $data["profilable_type"]);

            // Extraire les relations avant la mise à jour
            $roles = $data['roles'] ?? [];
            $users = $data['users'] ?? [];
            $permissions = $data['permissions'] ?? [];

            unset($data['roles'], $data['users'], $data['permissions']);

            // Mettre à jour les données de base du groupe
            $groupe->fill($data)->save();

            /**
             * ---- Gestion des Permissions ----
             */
            // Vérif rôles + récupération des permissions associées
            if (!empty($roles)) {
                $existingRoles = Role::whereIn('id', $roles)->get();
                if (count($existingRoles) !== count($roles)) {
                    DB::rollBack();
                    return response()->json([
                        'statut' => 'error',
                        'message' => 'Certains rôles spécifiés n\'existent pas',
                        'data' => null,
                        'statutCode' => Response::HTTP_BAD_REQUEST
                    ], Response::HTTP_BAD_REQUEST);
                }

                foreach ($existingRoles as $role) {
                    $rolePermissions = $role->permissions->pluck('id')->toArray();
                    $permissions = array_merge($permissions, $rolePermissions);
                }
            }

            // Supprimer doublons
            $permissions = array_unique($permissions);

            //dump("Permissions a mettre a jour". json_encode($permissions));

            if(count($permissions)){
                // Synchroniser les permissions
                $groupe->permissions()->sync($permissions);
            }

            /**
             * ---- Gestion des Utilisateurs ----
             */
            if (!empty($users)) {
                $finalUserIds = [];

                foreach ($users as $userData) {
                    if (isset($userData['id'])) {
                        // Utilisateur existant
                        $user = User::find($userData['id']);
                        if (!$user) {
                            DB::rollBack();
                            return response()->json([
                                'statut' => 'error',
                                'message' => 'Utilisateur avec ID ' . $userData['id'] . ' introuvable',
                                'data' => null,
                                'statutCode' => Response::HTTP_BAD_REQUEST
                            ], Response::HTTP_BAD_REQUEST);
                        }
                    } else {
                        // Création d’un nouvel utilisateur
                        $user = User::create([
                            'email' => $userData['email'],
                            'password' => bcrypt(Str::random(12)), // mot de passe aléatoire
                            'profilable_id' => $groupe->profilable_id,
                            'profilable_type' => $groupe->profilable_type,
                        ]);

                        // Création de la personne liée
                        if (!empty($userData['personne'])) {
                            $user->personne()->create([
                                'nom' => $userData['personne']['nom'],
                                'prenom' => $userData['personne']['prenom'],
                                'poste' => $userData['personne']['poste'] ?? null,
                            ]);
                        }
                    }

                    $finalUserIds[] = $user->id;
                }

                // Synchroniser avec le groupe
                $groupe->users()->sync($finalUserIds);
            }

            $groupe->refresh();

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a modifié le groupe d'utilisateurs {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Groupe d\'utilisateurs modifié avec succès',
                'data' => new GroupeUtilisateurResource($groupe->load(['permissions', 'users.personne', 'profilable'])),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Obtenir les rôles d'un groupe.
     */
    public function getGroupRoles(int|string $groupeId): JsonResponse
    {
        try {
            $groupe = $this->repository->findById($groupeId);

            if (!$groupe) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Groupe d\'utilisateurs non trouvé',
                    'data' => null,
                    'statutCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }

            $roles = $groupe->roles()->with('permissions')->get();

            return response()->json([
                'statut' => 'success',
                'message' => 'Rôles du groupe récupérés avec succès',
                'data' => RoleResource::collection($roles),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Obtenir les utilisateurs d'un groupe.
     */
    public function getGroupUsers(int|string $groupeId): JsonResponse
    {
        try {
            $groupe = $this->repository->findById($groupeId);

            if (!$groupe) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Groupe d\'utilisateurs non trouvé',
                    'data' => null,
                    'statutCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }

            $users = $groupe->users()->with(['personne', 'role'])->get();

            return response()->json([
                'statut' => 'success',
                'message' => 'Utilisateurs du groupe récupérés avec succès',
                'data' => UserResource::collection($users),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Créer un utilisateur et l'ajouter à un groupe.
     */
    public function createUserInGroup(int|string $groupeId, array $userData): JsonResponse
    {
        DB::beginTransaction();

        try {
            $groupe = $this->repository->findById($groupeId);

            if (!$groupe) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Groupe d\'utilisateurs non trouvé',
                    'data' => null,
                    'statutCode' => Response::HTTP_NOT_FOUND
                ], Response::HTTP_NOT_FOUND);
            }

            // Créer la personne
            $personneData = $userData['personne'] ?? [];
            $personne = $this->personneRepository->create($personneData);

            // Générer un mot de passe temporaire
            $password = $this->generateSimpleTemporaryPassword();

            // Préparer les données utilisateur
            $userCreateData = array_merge($userData, [
                'password' => Hash::make($password),
                'personneId' => $personne->id,
                'account_verification_request_sent_at' => Carbon::now(),
                'link_is_valide' => true,
                'provider' => 'local',
                'provider_user_id' => $userData['email'],
                'username' => $userData['email']
            ]);

            // Créer l'utilisateur
            $user = $this->userRepository->create($userCreateData);

            // Générer le token de vérification
            $user->token = str_replace(['/', '\\', '.'], '', Hash::make(
                $user->id . Hash::make($user->email) . Hash::make(strtotime($user->account_verification_request_sent_at))
            ));
            $user->save();

            // Ajouter l'utilisateur au groupe
            $groupe->users()->attach($user->id);

            // Assigner les rôles du groupe à l'utilisateur si spécifié
            if (isset($userData['change_password_first_login']) && $userData['change_password_first_login']) {
                $user->password_update_at = null;
            }

            DB::commit();

            // Envoyer l'email d'inscription
            //dispatch(new SendEmailJob($user, "confirmation-compte", $password))->delay(now()->addSeconds(15));

            //dispatch(new SendEmailJob($user, "confirmation-de-compte"))->delay(now()->addSeconds(15));

            dispatch(new SendEmailJob($user, "confirmation-compte", $password))->delay(now()->addMinutes(1));

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a créé l'utilisateur {$user->username} dans le groupe {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Utilisateur créé et ajouté au groupe avec succès',
                'data' => $user->load(['personne', 'groupesUtilisateur']),
                'statutCode' => Response::HTTP_CREATED
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null,
                'statutCode' => Response::HTTP_INTERNAL_SERVER_ERROR
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Assigner les permissions des rôles à un groupe.
     */
    public function assignRoles(int|string $groupeId, array $rolesIds): JsonResponse
    {
        DB::beginTransaction();
        try {
            $groupe = $this->repository->findById($groupeId);
            if (!$groupe) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Groupe introuvable',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }

            $roles = Role::whereIn('id', $rolesIds)->get();
            if ($roles->count() !== count($rolesIds)) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Certains rôles n\'existent pas',
                    'data' => null
                ], Response::HTTP_BAD_REQUEST);
            }

            $permissionsIds = [];
            foreach ($roles as $role) {
                $permissionsIds = array_merge(
                    $permissionsIds,
                    $role->permissions()->pluck('id')->toArray()
                );
            }

            $permissionsIds = array_unique($permissionsIds);
            $groupe->permissions()->syncWithoutDetaching($permissionsIds);

            DB::commit();

            return response()->json([
                'statut' => 'success',
                'message' => 'Permissions des rôles assignées au groupe',
                'data' => $groupe->load('permissions')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retirer les permissions associées à des rôles d’un groupe.
     */
    public function detachRoles(int|string $groupeId, array $rolesIds): JsonResponse
    {
        DB::beginTransaction();
        try {
            $groupe = $this->repository->findById($groupeId);
            if (!$groupe) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Groupe introuvable',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }

            $roles = Role::whereIn('id', $rolesIds)->get();
            if ($roles->count() !== count($rolesIds)) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Certains rôles n\'existent pas',
                    'data' => null
                ], Response::HTTP_BAD_REQUEST);
            }

            $permissionsIds = [];
            foreach ($roles as $role) {
                $permissionsIds = array_merge(
                    $permissionsIds,
                    $role->permissions()->pluck('id')->toArray()
                );
            }

            $permissionsIds = array_unique($permissionsIds);
            $groupe->permissions()->detach($permissionsIds);

            DB::commit();
            return response()->json([
                'statut' => 'success',
                'message' => 'Permissions des rôles détachées du groupe',
                'data' => $groupe->load('permissions')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Assigner des permissions directement à un groupe.
     */
    public function assignPermissions(int|string $groupeId, array $permissionsIds): JsonResponse
    {
        DB::beginTransaction();
        try {
            $groupe = $this->repository->findById($groupeId);
            if (!$groupe) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Groupe introuvable',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }

            $existingPermissions = Permission::whereIn('id', $permissionsIds)->pluck('id')->toArray();
            if (count($existingPermissions) !== count($permissionsIds)) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Certaines permissions n\'existent pas',
                    'data' => null
                ], Response::HTTP_BAD_REQUEST);
            }

            $groupe->permissions()->syncWithoutDetaching($permissionsIds);

            DB::commit();

            return response()->json([
                'statut' => 'success',
                'message' => 'Permissions assignées au groupe',
                'data' => $groupe->load('permissions')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retirer des permissions d’un groupe.
     */
    public function detachPermissions(int|string $groupeId, array $permissionsIds): JsonResponse
    {
        DB::beginTransaction();
        try {
            $groupe = $this->repository->findById($groupeId);
            if (!$groupe) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Groupe introuvable',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }

            $groupe->permissions()->detach($permissionsIds);
            DB::commit();

            return response()->json([
                'statut' => 'success',
                'message' => 'Permissions retirées du groupe',
                'data' => $groupe->load('permissions')
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Ajouter des utilisateurs à un groupe.
     */
    public function addUsers(int|string $groupeId, array $usersData): JsonResponse
    {
        DB::beginTransaction();

        try {

            $groupe = $this->repository->findById($groupeId);


            $attachedUsers = [];

            foreach ($usersData as $userData) {
                if (isset($userData['id']) && !empty($userData['id'])) {
                    // Cas 1 : utilisateur existant
                    $user = User::where('id', $userData['id'])
                        ->whereNull('deleted_at')
                        ->firstOrFail();
                } else {
                    // Cas 2 : nouvel utilisateur
                    $user = User::create([
                        'email' => $userData['email'],
                        'nom'   => $userData['personne']['nom'],
                        'prenom' => $userData['personne']['prenom'],
                        'poste' => $userData['personne']['poste'] ?? null,
                    ]);
                }

                // Attacher l'utilisateur au groupe
                $groupe->users()->syncWithoutDetaching([$user->id]);
                $attachedUsers[] = $user;
            }

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a ajouté des utilisateurs au groupe {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Utilisateurs ajoutés avec succès au groupe',
                'data' => $groupe->load('users.personne'),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Retirer des utilisateurs d’un groupe.
     */
    public function removeUsers(int|string $groupeId, array $usersIds): JsonResponse
    {
        DB::beginTransaction();
        try {

            $groupe = $this->repository->findById($groupeId);
            if (!$groupe) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Groupe introuvable',
                    'data' => null
                ], Response::HTTP_NOT_FOUND);
            }

            $groupe->users()->detach($usersIds);

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a retiré des utilisateurs du groupe {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Utilisateurs retirés avec succès du groupe',
                'data' => $groupe->load('users.personne'),
                'statutCode' => Response::HTTP_OK
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'statut' => 'error',
                'message' => $e->getMessage(),
                'data' => null
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
