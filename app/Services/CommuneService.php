<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\CommuneRepositoryInterface;
use App\Services\Contracts\CommuneServiceInterface;
use App\Http\Resources\CommuneResource;

class CommuneService extends BaseService implements CommuneServiceInterface
{
    public function __construct(CommuneRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CommuneResource::class;
    }
}