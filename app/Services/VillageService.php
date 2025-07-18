<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\VillageResource;
use App\Repositories\Contracts\VillageRepositoryInterface;
use App\Services\Contracts\VillageServiceInterface;

class VillageService extends BaseService implements VillageServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        VillageRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return VillageResource::class;
    }
}