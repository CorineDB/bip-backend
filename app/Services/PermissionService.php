<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\PermissionRepositoryInterface;
use App\Services\Contracts\PermissionServiceInterface;
use App\Http\Resources\PermissionResource;

class PermissionService extends BaseService implements PermissionServiceInterface
{
    public function __construct(PermissionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return PermissionResource::class;
    }
}