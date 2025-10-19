<?php

namespace App\Services;

use App\Http\Resources\CategorieProjetResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CategorieProjetRepositoryInterface;
use App\Services\Contracts\CategorieProjetServiceInterface;
use App\Services\Traits\CachableService;

class CategorieProjetService extends BaseService implements CategorieProjetServiceInterface
{
    use CachableService;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h - données géo changent rarement
    protected array $cacheTags = ['categories_projet'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    protected BaseRepositoryInterface $repository;

    public function __construct(
        CategorieProjetRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CategorieProjetResource::class;
    }

    /**
     * Récupère tous les Categorie des avec mise en cache
     */
    /*public function all(): JsonResponse
    {
        try {
            if ($this->cacheExists('all', [])) {
                $cached = $this->cacheGet('all', [], function() {
                    return null;
                });

                return (new $this->resourceClass($cached))->resolve();
                return response()->json($cached);
            }

            $responseData = [];
            foreach ($this->repository->getModel()->cursor() as $categorie_projet) {
                $responseData[] = (new $this->resourceClass($categorie_projet))->resolve();
            }

            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Récupère un Categorie de par son ID avec mise en cache
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

            $categorie_projet = $this->repository->findOrFail($id);
            $responseData = (new $this->resourceClass($categorie_projet))->resolve();
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Categorie de projet inconnu.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/
}
