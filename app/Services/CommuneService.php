<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\CommuneRepositoryInterface;
use App\Services\Contracts\CommuneServiceInterface;
use App\Http\Resources\CommuneResource;

class CommuneService extends BaseService implements CommuneServiceInterface
{
    public function __construct(CommuneRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CommuneResource::class;
    }

    public function arrondissements($idCommune): JsonResponse
    {
        try {
            $data = $this->repository->findOrFail($idCommune)->arrondissements;
            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}