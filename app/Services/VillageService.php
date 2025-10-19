<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Services\Traits\CachableService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\VillageResource;
use App\Repositories\Contracts\VillageRepositoryInterface;
use App\Services\Contracts\VillageServiceInterface;

class VillageService extends BaseService implements VillageServiceInterface
{
    use CachableService;

    protected BaseRepositoryInterface $repository;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h - données géographiques changent rarement
    protected array $cacheTags = ['geo', 'villages'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    public function __construct(
        VillageRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return VillageResource::class;
    }

    /**
     * Récupère tous les villages avec mise en cache (cursor + batch)
     */
    /*public function all(): JsonResponse
    {
        try {
            // Vérifier si les données sont en cache
            if ($this->cacheExists('all', [])) {
                $cached = $this->cacheGet('all', [], function() {
                    return null;
                });
                return response()->json($cached);
            }

            // Charger et transformer les villages avec cursor (économie mémoire)
            $responseData = [];
            foreach ($this->repository->getModel()->cursor() as $village) {
                $responseData[] = (new VillageResource($village))->resolve();
            }

            // Mettre en cache la réponse transformée
            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Récupère un village par son ID avec mise en cache
     */
    /*public function find(int|string $id): JsonResponse
    {
        try {
            // Vérifier si le village est en cache
            if ($this->cacheExists('find', ['id' => $id])) {
                $cached = $this->cacheGet('find', ['id' => $id], function() {
                    return null;
                });
                return response()->json($cached);
            }

            // Récupérer le village depuis la DB
            $village = $this->repository->findOrFail($id);

            // Transformer en resource
            $responseData = (new VillageResource($village))->resolve();

            // Mettre en cache la réponse transformée
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Village non trouvé.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/
}
