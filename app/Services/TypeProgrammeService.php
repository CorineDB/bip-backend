<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\TypeProgrammeResource;
use App\Repositories\Contracts\TypeProgrammeRepositoryInterface;
use App\Services\Contracts\TypeProgrammeServiceInterface;

class TypeProgrammeService extends BaseService implements TypeProgrammeServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        TypeProgrammeRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return TypeProgrammeResource::class;
    }
}
