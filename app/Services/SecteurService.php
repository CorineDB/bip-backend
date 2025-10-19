<?php

namespace App\Services;

use App\Http\Resources\DetailsSecteurResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Services\Traits\CachableService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\SecteurResource;
use App\Http\Resources\SecteurResourcePublic;
use App\Repositories\Contracts\SecteurRepositoryInterface;
use App\Services\Contracts\SecteurServiceInterface;

class SecteurService extends BaseService implements SecteurServiceInterface
{
    use CachableService;

    protected BaseRepositoryInterface $repository;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h
    protected array $cacheTags = ['secteurs'];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    public function __construct(
        SecteurRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return SecteurResource::class;
    }

    /**
     * Tous les secteurs avec cache
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
                $responseData[] = (new SecteurResource($item))->resolve();
            }

            $this->cachePut('all', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Un secteur par ID avec cache
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
            $responseData = (new SecteurResource($item))->resolve();
            $this->cachePut('find', $responseData, ['id' => $id]);

            return response()->json($responseData);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Secteur non trouvé.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }*/

    /**
     * Tous les secteurs détaillés avec cache
     */
    public function all_secteurs(): JsonResponse {
        try {

            $data = $this->repository->getModel()->where('type', 'grand-secteur')->get();

            return DetailsSecteurResource::collection($data)->response();

            if ($this->cacheExists('all_secteurs', [])) {
                $cached = $this->cacheGet('all_secteurs', [], function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->getModel()->where('type', 'grand-secteur')->get();

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new DetailsSecteurResource($item))->resolve();
            }

            $this->cachePut('all_secteurs', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Liste des grands secteurs avec cache
     */
    public function grands_secteurs(): JsonResponse{
        try {

            $data = $this->repository->getModel()->where('type', 'grand-secteur')->get();

            return SecteurResourcePublic::collection($data)->response();

            if ($this->cacheExists('grands_secteurs', [])) {
                $cached = $this->cacheGet('grands_secteurs', [], function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->getModel()->where('type', 'grand-secteur')->get();

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new SecteurResourcePublic($item))->resolve();
            }

            $this->cachePut('grands_secteurs', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Secteurs d'un grand secteur avec cache
     */
    public function secteurs_grand_secteur($idGrandSecteur): JsonResponse
    {
        try {

            $data = $this->repository->findOrFail($idGrandSecteur)->children;

            return SecteurResourcePublic::collection($data)->response();

            $params = ['grand_secteur_id' => $idGrandSecteur];

            if ($this->cacheExists('secteurs_grand_secteur', $params)) {
                $cached = $this->cacheGet('secteurs_grand_secteur', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->findOrFail($idGrandSecteur)->children;

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new SecteurResourcePublic($item))->resolve();
            }

            $this->cachePut('secteurs_grand_secteur', $responseData, $params, 43200);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Tous les secteurs (type=secteur) avec cache
     */
    public function secteurs(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->where('type', 'secteur')->whereHas('parent', function($query){
                $query->where('type', 'grand-secteur');
            })->get();

            return SecteurResourcePublic::collection($data)->response();

            if ($this->cacheExists('secteurs', [])) {
                $cached = $this->cacheGet('secteurs', [], function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->getModel()->where('type', 'secteur')->whereHas('parent', function($query){
                $query->where('type', 'grand-secteur');
            })->get();

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new SecteurResourcePublic($item))->resolve();
            }

            $this->cachePut('secteurs', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Tous les sous-secteurs avec cache
     */
    public function sous_secteurs(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->where('type', 'sous-secteur')->whereHas('parent', function($query){
                $query->where('type', 'secteur');
            })->get();

            return SecteurResourcePublic::collection($data)->response();

            if ($this->cacheExists('sous_secteurs', [])) {
                $cached = $this->cacheGet('sous_secteurs', [], function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->getModel()->where('type', 'sous-secteur')->whereHas('parent', function($query){
                $query->where('type', 'secteur');
            })->get();

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new SecteurResourcePublic($item))->resolve();
            }

            $this->cachePut('sous_secteurs', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Sous-secteurs d'un secteur avec cache
     */
    public function sous_secteurs_secteur($idSecteur): JsonResponse
    {
        try {

            $data = $this->repository->findOrFail($idSecteur)->children;

            return SecteurResourcePublic::collection($data)->response();

            $data = $this->repository->findOrFail($idSecteur)->children;

            return $this->resourceClass::collection($data)->response();

            $params = ['secteur_id' => $idSecteur];

            if ($this->cacheExists('sous_secteurs_secteur', $params)) {
                $cached = $this->cacheGet('sous_secteurs_secteur', $params, function() {
                    return null;
                });
                return response()->json($cached);
            }

            $data = $this->repository->findOrFail($idSecteur)->children;

            $responseData = [];
            foreach ($data as $item) {
                $responseData[] = (new SecteurResourcePublic($item))->resolve();
            }

            $this->cachePut('sous_secteurs_secteur', $responseData, $params, 43200);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
