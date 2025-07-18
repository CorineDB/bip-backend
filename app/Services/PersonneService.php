<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\PersonneRepositoryInterface;
use App\Services\Contracts\PersonneServiceInterface;
use App\Http\Resources\PersonneResource;

class PersonneService extends BaseService implements PersonneServiceInterface
{
    public function __construct(PersonneRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return PersonneResource::class;
    }
}