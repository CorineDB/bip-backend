<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\PersonneRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Http\Resources\UserResource;
use App\Jobs\SendEmailJob;
use App\Repositories\Contracts\OrganisationRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Traits\GenerateTemporaryPassword;
use App\Services\AuthService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService implements UserServiceInterface
{
    use GenerateTemporaryPassword;

    protected OrganisationRepositoryInterface $organisationRepository;
    protected PersonneRepositoryInterface $personneRepository;
    protected RoleRepositoryInterface $roleRepository;
    protected AuthService $authService;

    public function __construct(
        UserRepositoryInterface $repository,
        OrganisationRepositoryInterface $organisationRepository,
        PersonneRepositoryInterface $personneRepository,
        RoleRepositoryInterface $roleRepository,
        AuthService $authService
    ) {
        parent::__construct($repository);
        $this->organisationRepository = $organisationRepository;
        $this->personneRepository = $personneRepository;
        $this->roleRepository = $roleRepository;
        $this->authService = $authService;
    }

    protected function getResourceClass(): string
    {
        return UserResource::class;
    }

    public function all(): JsonResponse
    {
        try {

            $user = Auth::user();

            $query = $this->repository->getModel()/*
                ->whereNotIn("type", ["super-admin", "organisation", "dpaf", "dgpd"])*/
                ->where("profilable_id", $user->profilable_id)
                ->where("profilable_type", $user->profilable_type);

            // Si l'utilisateur appartient aux scopes organisation, dpaf, ou dgpd,
            // exclure les utilisateurs admin du scope
            /* if (!is_null($user->profilable_type) && in_array($user->profilable_type, ['App\Models\Organisation', 'App\Models\Dpaf', 'App\Models\Dgpd'])) {
                $query->whereHas('role', function ($roleQuery) {
                    $roleQuery->whereNotIn('slug', '!=', ['organisation', 'dpaf', 'dgpd']);
                });
            } */

            $item = $query->get();

            return ($this->resourceClass::collection($item->load(['role', 'groupesUtilisateur'])))->response();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            if (!($role = $this->roleRepository->findById($data["roleId"]))) throw new Exception("Role introuvable", 400);

            //if (!(auth()->user()->hasRole("administrateur", "super-admin", "super-administrateur", "organisation", "dpaf", "dgpd")))  throw new Exception("L'utilisateur a un rôle inconnu", 400);

            $password = $this->generateSimpleTemporaryPassword();

            $data['password'] = Hash::make($password);

            // Extraction des données de la personne
            $personneData = $data['personne'] ?? [];

            if (auth()->user()->profileable) {
                if (get_class(auth()->user()->profileable) == "App\\Models\\Organisation") {
                    $personneData["organismeId"] = auth()->user()->personne->organismeId;
                } else if ((get_class(auth()->user()->profileable) == "App\\Models\\Dpaf") || (get_class(auth()->user()->profileable) == "App\\Models\\Dgpd")) {

                    if (isset($personneData["organismeId"])) {

                        if (!($organisation = $this->organisationRepository->findById($personneData["organismeId"]))) throw new Exception("Organisation introuvable", 400);

                        $personneData["organismeId"] = $organisation->id;
                    } else if (auth()->user()->personne) {
                        $personneData["organismeId"] = auth()->user()->personne?->organismeId;
                    }
                }
            } else {
                $personneData["organismeId"] = null;
            }

            // Création de la personne
            $personne = $this->personneRepository->create($personneData);

            // Attribution de l'ID de la personne à l'utilisateur
            $data['personneId'] = $personne->id;
            $data['provider_user_id'] = $data['email'];
            $data['username'] = $data['email'];

            // Suppression des données de personne du tableau de données utilisateur
            unset($data['personne']);

            $profilable_id =  (auth()->user()->hasRole("administrateur", "super-admin", "super-administrateur")) ? null : Auth::user()->profilable_id;
            $profilable_type =  (auth()->user()->hasRole("administrateur", "super-admin", "super-administrateur")) ? null : Auth::user()->profilable_type;

            $user = $this->repository->create(array_merge($data, ["roleId" => $role->id, 'type' => $role->slug, 'profilable_type' => $profilable_type, 'profilable_id' => $profilable_id]));

            // Création de la personne

            $user->roles()->attach([$role->id]);

            $user->refresh();

            $user->account_verification_request_sent_at = Carbon::now();

            $user->token = str_replace(['/', '\\', '.'], '', Hash::make($user->id . Hash::make($user->email) . Hash::make(Hash::make(strtotime($user->account_verification_request_sent_at)))));

            $user->link_is_valide = true;

            $user->save();


            // Créer l'utilisateur dans Keycloak aussi

            /*
                $keycloakId = $this->authService->createKeycloakUser([
                    'email' => $user->email,
                    'username' => $user->username,
                    'first_name' => $personne->prenom ?? '',
                    'last_name' => $personne->nom ?? '',
                    'password' => $data['password']
                ]);

                // Mettre à jour l'utilisateur avec le keycloak_id
                if ($keycloakId) {
                    $user->update(['keycloak_id' => $keycloakId]);
                } else {
                    // Log warning but don't fail the user creation
                    \Log::warning('User created in Laravel but failed to create in Keycloak', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                    DB::rollBack();
                }
            */

            DB::commit();

            //Envoyer les identifiants de connexion à l'utilisateur via son email
            dispatch(new SendEmailJob($user, "confirmation-de-compte"))->delay(now()->addSeconds(15));

            dispatch(new SendEmailJob($user, "confirmation-compte", $password))->delay(now()->addMinutes(1));

            return (new $this->resourceClass($user))
                ->additional(['message' => 'Utilisateur créé avec succès.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function update(int|string $id, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Récupération de l'utilisateur
            $user = $this->repository->findOrFail($id);

            // Extraction des données de la personne
            $personneData = $data['personne'] ?? [];

            // Hash du mot de passe si fourni
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Mise à jour de la personne si des données sont fournies
            if (!empty($personneData) && $user->personneId) {
                $this->personneRepository->update($user->personneId, $personneData);
            }

            // Suppression des données de personne du tableau de données utilisateur
            unset($data['personne']);

            // Mise à jour de l'utilisateur
            $updated = $this->repository->update($id, $data);

            if (!$updated) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Utilisateur non trouvé ou non mis à jour.',
                ], 404);
            }

            // Récupération de l'utilisateur mis à jour
            $updatedUser = $this->repository->findOrFail($id);

            DB::commit();

            return (new $this->resourceClass($updatedUser))
                ->additional(['message' => 'Utilisateur mis à jour avec succès.'])
                ->response();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }
}
