<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Services\Traits\CachableService;
use App\Repositories\Contracts\ArrondissementRepositoryInterface;
use App\Services\Contracts\ArrondissementServiceInterface;
use App\Http\Resources\ArrondissementResource;
use App\Http\Resources\VillageResource;

class ArrondissementService extends BaseService implements ArrondissementServiceInterface
{
    use CachableService;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h
    protected array $cacheTags = ['geo', 'arrondissements'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    public function __construct(ArrondissementRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return ArrondissementResource::class;
    }

    /**
     * Récupère tous les arrondissements avec mise en cache
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
            foreach ($this->repository->getModel()->cursor() as $arrondissement) {
                $responseData[] = (new ArrondissementResource($arrondissement))->resolve();
            }

            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Récupère un arrondissement par son ID avec mise en cache
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

            $arrondissement = $this->repository->findOrFail($id);
            $responseData = (new ArrondissementResource($arrondissement))->resolve();
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Arrondissement non trouvé.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Récupère les villages d'un arrondissement avec mise en cache
     */
    public function villages($idArrondissement): JsonResponse
    {
        try {

            $villages = $this->repository->findOrFail($idArrondissement)->villages;

            return $this->resourceClass::collection($villages)->response();

            $params = ['arrondissement_id' => $idArrondissement];

            if ($this->cacheExists('villages', $params)) {
                $cached = $this->cacheGet('villages', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $villages = $this->repository->findOrFail($idArrondissement)->villages;

            $responseData = [];
            foreach ($villages as $village) {
                $responseData[] = (new VillageResource($village))->resolve();
            }

            // TTL réduit à 12h
            $this->cachePut('villages', $responseData, $params, 43200);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
