<?php

namespace App\Services;

use App\Http\Resources\ComposantProgrammeResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\ComposantProgrammeRepositoryInterface;
use App\Services\Contracts\ComposantProgrammeServiceInterface;
use App\Services\Traits\CachableService;

class ComposantProgrammeService extends BaseService implements ComposantProgrammeServiceInterface
{
    use CachableService;

    // Configuration du cache
    protected int $cacheTtl = 86400; // 24h - données géo changent rarement
    protected array $cacheTags = ['programme', "composants_programme"];
    protected string $cachePrefix = 'bip';
    protected bool $cacheEnabled = true;

    protected BaseRepositoryInterface $repository;

    public function __construct(
        ComposantProgrammeRepositoryInterface $repository
    ) {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return ComposantProgrammeResource::class;
    }

    /**
     * Récupère tous les Categorie des avec mise en cache
     */
    /*public function all(): JsonResponse
    {
        try {
            if ($this->cacheExists('all', [])) {
                $cached = $this->cacheGet('all', [], function () {
                    return null;
                });

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
                $cached = $this->cacheGet('find', ['id' => $id], function () {
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

    // Define contract methods here

    /**
     * Les axes du pag
     */
    public function axes_pag(): JsonResponse
    {

        try {
            $data = $this->repository->getModel()->whereHas('typeProgramme', function ($query) {
                    $query->where('slug', 'axe-pag');
                })->get();

            return $this->resourceClass::collection($data)->response();

            if ($this->cacheExists('axes_pag', [])) {
                $cached = $this->cacheGet('axes_pag', [], function () {
                    return null;
                });

                return response()->json($cached);
            }

            $responseData = [];
            foreach (
                $this->repository->getModel()->whereHas('typeProgramme', function ($query) {
                    $query->where('slug', 'axe-pag');
                })->cursor() as $axe_pag
            ) {
                $responseData[] = (new $this->resourceClass($axe_pag))->resolve();
            }

            $this->cachePut('axes_pag', $responseData, []);

            return response()->json($responseData);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function ($query) {
                $query->where('slug', 'axe-pag');
            })->get();

            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Les piliers du pag
     */
    public function piliers_pag(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function ($query) {
                $query->where('slug', 'pilier-pag');
            })->get();

            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Les actions du pag
     */
    public function actions_pag(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function ($query) {
                $query->where('slug', 'action-pag');
            })->get();

            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Liste des orientations strategique du PND
     */
    public function orientations_strategiques_pnd(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function ($query) {
                $query->where('slug', 'orientation-strategique-pnd');
            })->get();

            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Liste des objectifs strategique du PND
     */
    public function objectifs_strategiques_pnd(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function ($query) {
                $query->where('slug', 'objectif-strategique-pnd');
            })->get();

            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Liste des resultats strategique du PND
     */
    public function resultats_strategiques_pnd(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function ($query) {
                $query->where('slug', 'resultats-strategique-pnd');
            })->get();

            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }


    public function composants_de_programme($idComposantTypeProgramme): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->where('typeId', $idComposantTypeProgramme)->whereHas('typeProgramme', function ($query) use ($idComposantTypeProgramme) {
                $query->where('id', $idComposantTypeProgramme);
            })->get();

            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
