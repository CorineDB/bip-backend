<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\DepartementResource;
use App\Repositories\Contracts\DepartementRepositoryInterface;
use App\Services\Contracts\DepartementServiceInterface;

class DepartementService extends BaseService implements DepartementServiceInterface
{
    protected BaseRepositoryInterface $repository;

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

    public function communes($idDepartement): JsonResponse
    {
        try {
            $data = $this->repository->findOrFail($idDepartement)->communes;
            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}