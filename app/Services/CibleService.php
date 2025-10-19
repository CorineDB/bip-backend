<?php

namespace App\Services;

use App\Http\Resources\CibleResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CibleRepositoryInterface;
use App\Services\Contracts\CibleServiceInterface;
use App\Services\Traits\CachableService;

class CibleService extends BaseService implements CibleServiceInterface
{
    use CachableService;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h - données géo changent rarement
    protected array $cacheTags = ['cibles'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    protected BaseRepositoryInterface $repository;

    public function __construct(
        CibleRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CibleResource::class;
    }

    /**
     * Récupère tous les cibles avec mise en cache
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
                $responseData[] = (new $this->resourceClass($departement))->resolve();
            }

            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Récupère un cible par son ID avec mise en cache
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
            $responseData = (new $this->resourceClass($departement))->resolve();
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cible inconnu.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/
}
