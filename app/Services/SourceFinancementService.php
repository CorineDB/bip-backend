<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use App\Repositories\Contracts\SourceFinancementRepositoryInterface;
use App\Services\Contracts\SourceFinancementServiceInterface;

class SourceFinancementService extends BaseService implements SourceFinancementServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected ApiResourceInterface $resource;

    public function __construct(
        SourceFinancementRepositoryInterface $repository,
        ApiResourceInterface $resource
    )
    {
        parent::__construct($repository, $resource);
    }
}
