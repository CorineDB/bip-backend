<?php

namespace App\Services;

use App\Http\Resources\ComposantProgrammeResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\ComposantProgrammeRepositoryInterface;
use App\Services\Contracts\ComposantProgrammeServiceInterface;

class ComposantProgrammeService extends BaseService implements ComposantProgrammeServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        ComposantProgrammeRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return ComposantProgrammeResource::class;
    }
}
