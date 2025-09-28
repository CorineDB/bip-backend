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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class DepartementService extends BaseService implements DepartementServiceInterface
{
    //use CachableService;

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

    public function all(): JsonResponse
    {
        try {
            /* $departements = $this->cacheGet('all', [], function () {
                return $this->repository->all();
            }); */

             $departements = $this->repository->all();

            return $this->resourceClass::collection($departements)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function find(int|string $id): JsonResponse
    {
        try {
            $departement = /* $this->cacheGet('find', ['id' => $id], function () use ($id) {
                return */ $this->repository->findOrFail($id);
            //});

            return (new $this->resourceClass($departement))->response();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Département inconnu.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function communes($idDepartement): JsonResponse
    {
        try {
            $communes = /* $this->cacheGet('communes', ['departement_id' => $idDepartement], function () use ($idDepartement) {
                return  */$this->repository->findOrFail($idDepartement)->communes;
            //}, 43200); // 12h pour les communes

            return CommuneResource::collection($communes)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function create(array $data): JsonResponse
    {
        try {
            $departement = $this->repository->create($data);

            // Invalider et reconstruire le cache automatiquement
            $this->refreshCacheAfterUpdate();

            return (new $this->resourceClass($departement))->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function update($id, array $data): JsonResponse
    {
        try {
            $departement = $this->repository->update($id, $data);

            // Invalider et reconstruire le cache automatiquement
            $this->refreshCacheAfterUpdate();

            return (new $this->resourceClass($departement))->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $this->repository->delete($id);

            // Invalider et reconstruire le cache automatiquement
            $this->refreshCacheAfterUpdate();

            return response()->json(['message' => 'Département supprimé avec succès']);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Invalide et reconstruit automatiquement le cache après modification
     */
    protected function refreshCacheAfterUpdate(): void
    {
        // Configuration des caches à reconstruire automatiquement
        $refreshMethods = [
            'all' => [
                'method' => 'all',
                'params' => [],
                'callback' => fn() => $this->repository->all(),
                'ttl' => $this->cacheTtl
            ]
        ];

        // Utilise la méthode générique du trait
        //$this->refreshAllCache($refreshMethods);

        // Optionnel: pré-charger les caches les plus utilisés
        $this->preloadPopularCaches();
    }

    /**
     * Pré-charge les caches populaires en arrière-plan
     */
    protected function preloadPopularCaches(): void
    {
        try {
            // Récupérer les 3 premiers départements et pré-charger leurs communes
            $topDepartements = $this->repository->all();

            /* foreach ($topDepartements as $departement) {
                $this->refreshCache('communes',
                    ['departement_id' => $departement->id],
                    fn() => $departement->communes,
                    43200
                );
            } */
        } catch (Exception $e) {
            \Log::info('Preload cache skipped: ' . $e->getMessage());
        }
    }
}
