<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\OrganisationRepositoryInterface;
use App\Services\Contracts\OrganisationServiceInterface;
use App\Http\Resources\OrganisationResource;

class OrganisationService extends BaseService implements OrganisationServiceInterface
{
    public function __construct(OrganisationRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return OrganisationResource::class;
    }
}