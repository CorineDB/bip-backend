<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\ProjetResource;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Services\Contracts\ProjetServiceInterface;

class ProjetService extends BaseService implements ProjetServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        ProjetRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return ProjetResource::class;
    }
}