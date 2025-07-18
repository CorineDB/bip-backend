<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\TypeInterventionResource;
use App\Repositories\Contracts\TypeInterventionRepositoryInterface;
use App\Services\Contracts\TypeInterventionServiceInterface;

class TypeInterventionService extends BaseService implements TypeInterventionServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        TypeInterventionRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return TypeInterventionResource::class;
    }
}
