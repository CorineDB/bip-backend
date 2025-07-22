<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\PersonneRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Http\Resources\UserResource;
use App\Traits\GenerateTemporaryPassword;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService implements UserServiceInterface
{
    use GenerateTemporaryPassword;

    protected PersonneRepositoryInterface $personneRepository;
    protected AuthService $authService;

    public function __construct(
        UserRepositoryInterface $repository,
        PersonneRepositoryInterface $personneRepository,
        AuthService $authService
    ) {
        parent::__construct($repository);
        $this->personneRepository = $personneRepository;
        $this->authService = $authService;
    }

    protected function getResourceClass(): string
    {
        return UserResource::class;
    }

    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            // Extraction des données de la personne
            $personneData = $data['personne'] ?? [];

            $data['password'] = Hash::make($this->generateSimpleTemporaryPassword());

            // Création de la personne
            $personne = $this->personneRepository->create($personneData);

            // Attribution de l'ID de la personne à l'utilisateur
            $data['personneId'] = $personne->id;
            $data['provider_user_id'] = $data['email'];
            $data['username'] = $data['email'];

            // Suppression des données de personne du tableau de données utilisateur
            unset($data['personne']);

            // Création de l'utilisateur
            $user = $this->repository->create($data);

            // Créer l'utilisateur dans Keycloak aussi

            $keycloakId = $this->authService->createKeycloakUser([
                'email' => $user->email,
                'username' => $user->username,
                'first_name' => $personne->prenom ?? '',
                'last_name' => $personne->nom ?? '',
                'password' => $$data['password']
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

            DB::commit();

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