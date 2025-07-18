<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\IdeeProjetRepositoryInterface;
use App\Services\Contracts\IdeeProjetServiceInterface;
use App\Http\Resources\IdeeProjetResource;

class IdeeProjetService extends BaseService implements IdeeProjetServiceInterface
{
    public function __construct(IdeeProjetRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return IdeeProjetResource::class;
    }
}
