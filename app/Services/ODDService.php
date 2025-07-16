<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use App\Repositories\Contracts\ODDRepositoryInterface;
use App\Services\Contracts\ODDServiceInterface;

class ODDService extends BaseService implements ODDServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected ApiResourceInterface $resource;

    public function __construct(
        ODDRepositoryInterface $repository,
        ApiResourceInterface $resource
    )
    {
        parent::__construct($repository, $resource);
    }
}