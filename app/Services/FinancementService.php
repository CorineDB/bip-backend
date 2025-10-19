<?php

namespace App\Services;

use App\Http\Resources\FinancementPublicResource;
use App\Http\Resources\FinancementResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Services\Traits\CachableService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\FinancementRepositoryInterface;
use App\Services\Contracts\FinancementServiceInterface;

class FinancementService extends BaseService implements FinancementServiceInterface
{
    use CachableService;

    protected BaseRepositoryInterface $repository;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h
    protected array $cacheTags = ['financements'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    public function __construct(
        FinancementRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return FinancementResource::class;
    }

    /**
     * Tous les financements avec cache
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
                $responseData[] = (new FinancementResource($item))->resolve();
            }

            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Un financement par ID avec cache
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
            $responseData = (new FinancementResource($item))->resolve();
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Financement non trouvé.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Liste des types de financement avec cache
     */
    public function types_de_financement(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->where('type', 'type')->whereNull('financementId')->get();
            return ($this->resourceClass::collection($data))->response();

            if ($this->cacheExists('types_de_financement', [])) {
                $cached = $this->cacheGet('types_de_financement', [], function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->getModel()->where('type', 'type')->whereNull('financementId')->get();

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new FinancementPublicResource($item))->resolve();
            }

            $this->cachePut('types_de_financement', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Natures d'un type de financement avec cache
     */
    public function natures_type_de_financement($idType): JsonResponse
    {
        try {
            $data = $this->repository->findOrFail($idType)->children;

            return ($this->resourceClass::collection($data))->response();

            $params = ['type_id' => $idType];

            if ($this->cacheExists('natures_type_de_financement', $params)) {
                $cached = $this->cacheGet('natures_type_de_financement', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->findOrFail($idType)->children;

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new FinancementPublicResource($item))->resolve();
            }

            $this->cachePut('natures_type_de_financement', $responseData, $params, 43200);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Toutes les natures de financement avec cache
     */
    public function natures_de_financement(): JsonResponse
    {
        try {
            $data = $this->repository->getModel()->where('type', 'nature')->whereHas('parent', function($query){
                $query->where('type', 'type');
            })->get();

            return ($this->resourceClass::collection($data))->response();

            if ($this->cacheExists('natures_de_financement', [])) {
                $cached = $this->cacheGet('natures_de_financement', [], function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->getModel()->where('type', 'nature')->whereHas('parent', function($query){
                $query->where('type', 'type');
            })->get();

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new FinancementPublicResource($item))->resolve();
            }

            $this->cachePut('natures_de_financement', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Toutes les sources de financement avec cache
     */
    public function sources_de_financement(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->where('type', 'source')->whereHas('parent', function($query){
                $query->where('type', 'nature');
            })->get();

            return ($this->resourceClass::collection($data))->response();

            if ($this->cacheExists('sources_de_financement', [])) {
                $cached = $this->cacheGet('sources_de_financement', [], function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->getModel()->where('type', 'source')->whereHas('parent', function($query){
                $query->where('type', 'nature');
            })->get();

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new FinancementPublicResource($item))->resolve();
            }

            $this->cachePut('sources_de_financement', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Sources d'une nature de financement avec cache
     */
    public function sources_nature_de_financement($idNature): JsonResponse
    {
        try {

            $data = $this->repository->findOrFail($idNature)->children;

            return ($this->resourceClass::collection($data))->response();

            $params = ['nature_id' => $idNature];

            if ($this->cacheExists('sources_nature_de_financement', $params)) {
                $cached = $this->cacheGet('sources_nature_de_financement', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->findOrFail($idNature)->children;

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new FinancementPublicResource($item))->resolve();
            }

            $this->cachePut('sources_nature_de_financement', $responseData, $params, 43200);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
