<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\SecteurResource;
use App\Http\Resources\SecteurResourcePublic;
use App\Repositories\Contracts\SecteurRepositoryInterface;
use App\Services\Contracts\SecteurServiceInterface;

class SecteurService extends BaseService implements SecteurServiceInterface
{
    protected BaseRepositoryInterface $repository;

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
     * Liste des grands secteurs
     *
     * @return JsonResponse
     */
    public function grands_secteurs(): JsonResponse{
        try {
            $data = $this->repository->getModel()->where('type', 'grand-secteur')->get();

            return SecteurResourcePublic::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function secteurs_grand_secteur($idGrandSecteur): JsonResponse
    {
        try {
            $data = $this->repository->findOrFail($idGrandSecteur)->children;
            return SecteurResourcePublic::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function secteurs(): JsonResponse
    {
        try {

            $data = $this->repository->getModel()->where('type', 'secteur')->whereHas('parent', function($query){
                $query->where('type', 'grand-secteur');
            })->get();
            return SecteurResourcePublic::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function sous_secteurs(): JsonResponse
    {
        try {
            $data = $this->repository->getModel()->where('type', 'sous-secteur')->whereHas('parent', function($query){
                $query->where('type', 'secteur');
            })->get();
            return SecteurResourcePublic::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function sous_secteurs_secteur($idSecteur): JsonResponse
    {
        try {
            $data = $this->repository->findOrFail($idSecteur)->children;
            return SecteurResourcePublic::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}