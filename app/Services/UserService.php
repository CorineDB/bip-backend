<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\PersonneRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserService extends BaseService implements UserServiceInterface
{
    protected PersonneRepositoryInterface $personneRepository;

    public function __construct(
        UserRepositoryInterface $repository,
        PersonneRepositoryInterface $personneRepository
    ) {
        parent::__construct($repository);
        $this->personneRepository = $personneRepository;
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

            // Hash du mot de passe
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            // Création de la personne
            $personne = $this->personneRepository->create($personneData);

            // Attribution de l'ID de la personne à l'utilisateur
            $data['personneId'] = $personne->id;

            // Suppression des données de personne du tableau de données utilisateur
            unset($data['personne']);

            // Création de l'utilisateur
            $user = $this->repository->create($data);

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