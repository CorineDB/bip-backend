<?php

namespace App\Services;

use App\Http\Resources\ProgrammeResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\TypeProgrammeResource;
use App\Repositories\Contracts\TypeProgrammeRepositoryInterface;
use App\Services\Contracts\TypeProgrammeServiceInterface;

class TypeProgrammeService extends BaseService implements TypeProgrammeServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        TypeProgrammeRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return TypeProgrammeResource::class;
    }


    public function programmes(): JsonResponse {
        try {
            $data = $this->repository->getModel()->whereNull('typeId')->whereNull("deleted_at")->get();

            return ProgrammeResource::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function composants_de_programme($idProgramme): JsonResponse {
        try {
            $data = $this->repository->getModel()->where('typeId', $idProgramme)->whereNull("deleted_at")->get();

            return TypeProgrammeResource::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function composants_composants_de_programme($idComposantProgramme): JsonResponse{
        try {
            $data = $this->repository->getModel()->where('typeId', $idComposantProgramme)->whereNull("deleted_at")->get();

            return TypeProgrammeResource::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
