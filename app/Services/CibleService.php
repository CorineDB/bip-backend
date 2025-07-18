<?php

namespace App\Services;

use App\Http\Resources\CibleResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CibleRepositoryInterface;
use App\Services\Contracts\CibleServiceInterface;

class CibleService extends BaseService implements CibleServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        CibleRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CibleResource::class;
    }
}