<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use App\Repositories\Contracts\WorkflowRepositoryInterface;
use App\Services\Contracts\WorkflowServiceInterface;

class WorkflowService extends BaseService implements WorkflowServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected ApiResourceInterface $resource;

    public function __construct(
        WorkflowRepositoryInterface $repository,
        ApiResourceInterface $resource
    )
    {
        parent::__construct($repository, $resource);
    }
}