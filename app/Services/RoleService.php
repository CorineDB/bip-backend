<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Contracts\RoleServiceInterface;

class RoleService extends BaseService implements RoleServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }
}