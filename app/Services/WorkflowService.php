<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\WorkflowResource;
use App\Repositories\Contracts\WorkflowRepositoryInterface;
use App\Services\Contracts\WorkflowServiceInterface;

class WorkflowService extends BaseService implements WorkflowServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        WorkflowRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return WorkflowResource::class;
    }
}