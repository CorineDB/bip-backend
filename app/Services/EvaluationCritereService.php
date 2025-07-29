<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use App\Http\Resources\EvaluationCritereResource;
use App\Repositories\Contracts\EvaluationCritereRepositoryInterface;
use App\Services\Contracts\EvaluationCritereServiceInterface;

class EvaluationCritereService extends BaseService implements EvaluationCritereServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected ApiResourceInterface $resource;

    public function __construct(
        EvaluationCritereRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return EvaluationCritereResource::class;
    }
}