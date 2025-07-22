<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\ArrondissementRepositoryInterface;
use App\Services\Contracts\ArrondissementServiceInterface;
use App\Http\Resources\ArrondissementResource;

class ArrondissementService extends BaseService implements ArrondissementServiceInterface
{
    public function __construct(ArrondissementRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return ArrondissementResource::class;
    }

    public function villages($idArrondissement): JsonResponse
    {
        try {
            $data = $this->repository->findOrFail($idArrondissement)->villages;
            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}