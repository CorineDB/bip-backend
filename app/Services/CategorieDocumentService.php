<?php

namespace App\Services;

use App\Http\Resources\CategorieDocumentResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CategorieDocumentRepositoryInterface;
use App\Services\Contracts\CategorieDocumentServiceInterface;
use App\Services\Traits\CachableService;

class CategorieDocumentService extends BaseService implements CategorieDocumentServiceInterface
{
    use CachableService;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h - données géo changent rarement
    protected array $cacheTags = ['canevas', 'categories_document'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    protected BaseRepositoryInterface $repository;
    public function __construct(
        CategorieDocumentRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CategorieDocumentResource::class;
    }

    /**
     * Récupère tous les Catégorie de document avec mise en cache
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
            foreach ($this->repository->getModel()->cursor() as $categorie_document) {
                $responseData[] = (new $this->resourceClass($categorie_document))->resolve();
            }

            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Récupère un Catégorie de document par son ID avec mise en cache
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

            $categorie_document = $this->repository->findOrFail($id);
            $responseData = (new $this->resourceClass($categorie_document))->resolve();
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Catégorie de document inconnu.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/
}
