<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\EvaluationResource;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Services\Contracts\EvaluationServiceInterface;

class EvaluationService extends BaseService implements EvaluationServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        EvaluationRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return EvaluationResource::class;
    }
}