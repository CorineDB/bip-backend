<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Services\Traits\CachableService;
use App\Repositories\Contracts\CommuneRepositoryInterface;
use App\Services\Contracts\CommuneServiceInterface;
use App\Http\Resources\CommuneResource;
use App\Http\Resources\ArrondissementResource;

class CommuneService extends BaseService implements CommuneServiceInterface
{
    use CachableService;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h
    protected array $cacheTags = ['geo', 'communes'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    public function __construct(CommuneRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CommuneResource::class;
    }

    /**
     * Récupère toutes les communes avec mise en cache
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
            foreach ($this->repository->getModel()->cursor() as $commune) {
                $responseData[] = (new CommuneResource($commune))->resolve();
            }

            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Récupère une commune par son ID avec mise en cache
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

            $commune = $this->repository->findOrFail($id);
            $responseData = (new CommuneResource($commune))->resolve();
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Commune non trouvée.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Récupère les arrondissements d'une commune avec mise en cache
     */
    public function arrondissements($idCommune): JsonResponse
    {
        try {

            $arrondissements = $this->repository->findOrFail($idCommune)->arrondissements;

            return $this->resourceClass::collection($arrondissements)->response();

            $params = ['commune_id' => $idCommune];

            if ($this->cacheExists('arrondissements', $params)) {
                $cached = $this->cacheGet('arrondissements', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $arrondissements = $this->repository->findOrFail($idCommune)->arrondissements;

            $responseData = [];
            foreach ($arrondissements as $arrondissement) {
                $responseData[] = (new ArrondissementResource($arrondissement))->resolve();
            }

            // TTL réduit à 12h
            $this->cachePut('arrondissements', $responseData, $params, 43200);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
