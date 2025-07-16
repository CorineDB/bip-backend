<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use App\Repositories\Contracts\TypeProgrammeRepositoryInterface;
use App\Services\Contracts\TypeProgrammeServiceInterface;

class TypeProgrammeService extends BaseService implements TypeProgrammeServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected ApiResourceInterface $resource;

    public function __construct(
        TypeProgrammeRepositoryInterface $repository,
        ApiResourceInterface $resource
    )
    {
        parent::__construct($repository, $resource);
    }
}
