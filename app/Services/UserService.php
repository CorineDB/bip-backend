<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\Contracts\UserServiceInterface;

class UserService extends BaseService implements UserServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        parent::__construct($repository);
        $this->repository = $repository;
    }
}