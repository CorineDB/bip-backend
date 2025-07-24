<?php

namespace App\Services;

use App\Http\Resources\ComposantProgrammeResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\ComposantProgrammeRepositoryInterface;
use App\Services\Contracts\ComposantProgrammeServiceInterface;

class ComposantProgrammeService extends BaseService implements ComposantProgrammeServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        ComposantProgrammeRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return ComposantProgrammeResource::class;
    }


    // Define contract methods here

    /**
     * Les axes du pag
     */
    public function axes_pag(): JsonResponse{

        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function($query){
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
    public function piliers_pag(): JsonResponse{
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function($query){
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
    public function actions_pag(): JsonResponse{
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function($query){
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
    public function orientations_strategiques_pnd(): JsonResponse{
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function($query){
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
    public function objectifs_strategiques_pnd(): JsonResponse{
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function($query){
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
    public function resultats_strategiques_pnd(): JsonResponse {
        try {

            $data = $this->repository->getModel()->whereHas('typeProgramme', function($query){
                $query->where('slug', 'resultats-strategique-pnd');
            })->get();

            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
