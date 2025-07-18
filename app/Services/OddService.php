<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\OddResource;
use App\Repositories\Contracts\OddRepositoryInterface;
use App\Services\Contracts\OddServiceInterface;

class OddService extends BaseService implements OddServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        OddRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return OddResource::class;
    }
}