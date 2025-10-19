<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Services\Traits\CachableService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\DepartementResource;
use App\Http\Resources\CommuneResource;
use App\Repositories\Contracts\DepartementRepositoryInterface;
use App\Services\Contracts\DepartementServiceInterface;

class DepartementService extends BaseService implements DepartementServiceInterface
{
    use CachableService;

    protected BaseRepositoryInterface $repository;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h - données géo changent rarement
    protected array $cacheTags = ['geo', 'departements'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    public function __construct(
        DepartementRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return DepartementResource::class;
    }

    /**
     * Récupère tous les départements avec mise en cache
     */
    /*public function all(): JsonResponse
    {
        try {
            if ($this->cacheExists('all', [])) {
                $cached = $this->cacheGet('all', [], function() {
                    return null;
                });
                return response()->json($cached);
            }

            $responseData = [];
            foreach ($this->repository->getModel()->cursor() as $departement) {
                $responseData[] = (new DepartementResource($departement))->resolve();
            }

            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupère un département par son ID avec mise en cache
     */
    /*public function find(int|string $id): JsonResponse
    {
        try {
            if ($this->cacheExists('find', ['id' => $id])) {
                $cached = $this->cacheGet('find', ['id' => $id], function() {
                    return null;
                });
                return response()->json($cached);
            }

            $departement = $this->repository->findOrFail($id);
            $responseData = (new DepartementResource($departement))->resolve();
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Département inconnu.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Récupère les communes d'un département avec mise en cache
     */
    public function communes($idDepartement): JsonResponse
    {
        try {

            $communes = $this->repository->findOrFail($idDepartement)->communes;

            return $this->resourceClass::collection($communes)->response();

            $params = ['departement_id' => $idDepartement];

            if ($this->cacheExists('communes', $params)) {
                $cached = $this->cacheGet('communes', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $communes = $this->repository->findOrFail($idDepartement)->communes;

            $responseData = [];
            foreach ($communes as $commune) {
                $responseData[] = (new CommuneResource($commune))->resolve();
            }

            // TTL réduit à 12h car plus dynamique
            $this->cachePut('communes', $responseData, $params, 43200);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
