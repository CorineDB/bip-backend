<?php

namespace App\Services;

use App\Http\Resources\FinancementPublicResource;
use App\Http\Resources\FinancementResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\FinancementRepositoryInterface;
use App\Services\Contracts\FinancementServiceInterface;

class FinancementService extends BaseService implements FinancementServiceInterface
{
    protected BaseRepositoryInterface $repository;

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
     * Liste des grands secteurs
     *
     * @return JsonResponse
     */
    public function types_de_financement(): JsonResponse
    {
        try {
            $data = $this->repository->getModel()->where('type', 'type')->whereNull('financementId')->get();

            return FinancementPublicResource::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function natures_type_de_financement($idType): JsonResponse
    {
        try {
            $data = $this->repository->findOrFail($idType)->children;
            return FinancementPublicResource::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function natures_de_financement(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->where('type', 'nature')->whereHas('parent', function($query){
                $query->where('type', 'type');
            })->get();
            return FinancementPublicResource::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function sources_de_financement(): JsonResponse
    {
        try {
            $data = $this->repository->getModel()->where('type', 'source')->whereHas('parent', function($query){
                $query->where('type', 'nature');
            })->get();
            return FinancementPublicResource::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function sources_nature_de_financement($idNature): JsonResponse
    {
        try {
            $data = $this->repository->findOrFail($idNature)->children;
            return FinancementPublicResource::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}