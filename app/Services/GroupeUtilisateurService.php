<?php

namespace App\Services;

use App\Jobs\SendEmailJob;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use App\Http\Resources\GroupeUtilisateurResource;
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

    /**
     * Create a new group of users.
     */
    public function create(array $data): JsonResponse
    {
        DB::beginTransaction();

        try {
            // Vérifier que le profil (DPAF/DGPD) existe
            /* if (isset($data['profilable_type']) && isset($data['profilable_id'])) {
                $profilableModel = $data['profilable_type'];
                $profilableExists = $profilableModel::find($data['profilable_id']);

                if (!$profilableExists) {
                    return response()->json([
                        'statut' => 'error',
                        'message' => 'Le profil spécifié n\'existe pas',
                        'data' => null,
                        'statutCode' => Response::HTTP_BAD_REQUEST
                    ], Response::HTTP_BAD_REQUEST);
                }
            } */

            $data['profilable_id'] = Auth::user()->profilable_id;
            $data['profilable_type'] = Auth::user()->profilable_type;

            // Créer le groupe
            $groupe = $this->repository->create($data);

            // Assigner les rôles si fournis
            if (isset($data['roles']) && is_array($data['roles'])) {
                $existingRoles = Role::whereIn('id', $data['roles'])->pluck('id')->toArray();
                if (count($existingRoles) === count($data['roles'])) {
                    $groupe->roles()->attach($data['roles']);
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

            // Ajouter les utilisateurs si fournis
            if (isset($data['users']) && is_array($data['users'])) {
                $existingUsers = User::whereIn('id', $data['users'])->pluck('id')->toArray();
                if (count($existingUsers) === count($data['users'])) {
                    $groupe->users()->attach($data['users']);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'statut' => 'error',
                        'message' => 'Certains utilisateurs spécifiés n\'existent pas',
                        'data' => null,
                        'statutCode' => Response::HTTP_BAD_REQUEST
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a créé le groupe d'utilisateurs {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Groupe d\'utilisateurs créé avec succès',
                'data' => $groupe->load(['roles', 'users', 'profilable']),
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

            unset($data["profilable_id"]);
            unset($data["profilable_type"]);

            // Extraire les relations avant la mise à jour
            $roles = $data['roles'] ?? null;
            $users = $data['users'] ?? null;
            unset($data['roles'], $data['users']);

            // Mettre à jour les données de base du groupe
            $groupe->fill($data)->save();

            // Synchroniser les rôles si fournis
            if ($roles !== null && is_array($roles)) {
                $existingRoles = Role::whereIn('id', $roles)->pluck('id')->toArray();
                if (count($existingRoles) === count($roles)) {
                    $groupe->roles()->sync($roles);
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

            // Synchroniser les utilisateurs si fournis
            if ($users !== null && is_array($users)) {
                $existingUsers = User::whereIn('id', $users)->pluck('id')->toArray();
                if (count($existingUsers) === count($users)) {
                    $groupe->users()->sync($users);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'statut' => 'error',
                        'message' => 'Certains utilisateurs spécifiés n\'existent pas',
                        'data' => null,
                        'statutCode' => Response::HTTP_BAD_REQUEST
                    ], Response::HTTP_BAD_REQUEST);
                }
            }

            $groupe->refresh();
            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a modifié le groupe d'utilisateurs {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Groupe d\'utilisateurs modifié avec succès',
                'data' => $groupe->load(['roles', 'users', 'profilable']),
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
     * Assigner des rôles à un groupe d'utilisateurs.
     */
    public function assignRoles(int|string $groupeId, array $rolesIds): JsonResponse
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

            // Vérifier que tous les rôles existent
            $existingRoles = Role::whereIn('id', $rolesIds)->pluck('id')->toArray();
            $missingRoles = array_diff($rolesIds, $existingRoles);

            if (!empty($missingRoles)) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Certains rôles n\'existent pas: ' . implode(', ', $missingRoles),
                    'data' => null,
                    'statutCode' => Response::HTTP_BAD_REQUEST
                ], Response::HTTP_BAD_REQUEST);
            }

            // Assigner les rôles au groupe
            $groupe->roles()->syncWithoutDetaching($rolesIds);

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a assigné des rôles au groupe {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Rôles assignés avec succès au groupe',
                'data' => $groupe->load('roles'),
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
     * Retirer des rôles d'un groupe d'utilisateurs.
     */
    public function detachRoles(int|string $groupeId, array $rolesIds): JsonResponse
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

            // Retirer les rôles du groupe
            $groupe->roles()->detach($rolesIds);

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a retiré des rôles du groupe {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Rôles retirés avec succès du groupe',
                'data' => $groupe->load('roles'),
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
     * Ajouter des utilisateurs à un groupe.
     */
    public function addUsers(int|string $groupeId, array $usersIds): JsonResponse
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

            // Vérifier que tous les utilisateurs existent
            $existingUsers = User::whereIn('id', $usersIds)->pluck('id')->toArray();
            $missingUsers = array_diff($usersIds, $existingUsers);

            if (!empty($missingUsers)) {
                return response()->json([
                    'statut' => 'error',
                    'message' => 'Certains utilisateurs n\'existent pas: ' . implode(', ', $missingUsers),
                    'data' => null,
                    'statutCode' => Response::HTTP_BAD_REQUEST
                ], Response::HTTP_BAD_REQUEST);
            }

            // Ajouter les utilisateurs au groupe
            $groupe->users()->syncWithoutDetaching($usersIds);

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a ajouté des utilisateurs au groupe {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Utilisateurs ajoutés avec succès au groupe',
                'data' => $groupe->load('users'),
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
     * Retirer des utilisateurs d'un groupe.
     */
    public function removeUsers(int|string $groupeId, array $usersIds): JsonResponse
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

            // Retirer les utilisateurs du groupe
            $groupe->users()->detach($usersIds);

            DB::commit();

            $acteur = Auth::check() ? Auth::user()->nom . " " . Auth::user()->prenom : "Inconnu";
            $message = Str::ucfirst($acteur) . " a retiré des utilisateurs du groupe {$groupe->nom}.";

            return response()->json([
                'statut' => 'success',
                'message' => 'Utilisateurs retirés avec succès du groupe',
                'data' => $groupe->load('users'),
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
                'data' => $roles,
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
                'data' => $users,
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
            dispatch(new SendEmailJob($user, "confirmation-de-compte", $password))->delay(now()->addSeconds(15));

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
}
