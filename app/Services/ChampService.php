<?php

namespace App\Services;

use App\Http\Resources\ChampResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\ChampRepositoryInterface;
use App\Services\Contracts\ChampServiceInterface;

class ChampService extends BaseService implements ChampServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        ChampRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return ChampResource::class;
    }
}