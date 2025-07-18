<?php

namespace App\Services;

use App\Http\Resources\FinancementResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\FinancementRepositoryInterface;
use App\Services\Contracts\FinancementServiceInterface;

class FinancementService extends BaseService implements FinancementServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        FinancementRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return FinancementResource::class;
    }
}