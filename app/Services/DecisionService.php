<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\DecisionRepositoryInterface;
use App\Services\Contracts\DecisionServiceInterface;
use App\Http\Resources\DecisionResource;

class DecisionService extends BaseService implements DecisionServiceInterface
{
    public function __construct(DecisionRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return DecisionResource::class;
    }
}