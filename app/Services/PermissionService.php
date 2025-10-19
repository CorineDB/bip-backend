<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Services\Traits\CachableService;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Services\Contracts\PermissionServiceInterface;
use App\Http\Resources\PermissionResource;

class PermissionService extends BaseService implements PermissionServiceInterface
{
    use CachableService;

    // Configuration du cache
    protected int $cacheTtl = 3600; // 1h - dépend des permissions utilisateur
    protected array $cacheTags = ['permissions'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    public function __construct(PermissionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return PermissionResource::class;
    }

    /**
     * Toutes les permissions avec cache
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
            foreach ($this->repository->getModel()->cursor() as $item) {
                $responseData[] = (new PermissionResource($item))->resolve();
            }

            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Une permission par ID avec cache
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

            $item = $this->repository->findOrFail($id);
            $responseData = (new PermissionResource($item))->resolve();
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Permission non trouvée.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/
}
