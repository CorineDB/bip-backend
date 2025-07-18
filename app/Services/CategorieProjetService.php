<?php

namespace App\Services;

use App\Http\Resources\CategorieProjetResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CategorieProjetRepositoryInterface;
use App\Services\Contracts\CategorieProjetServiceInterface;

class CategorieProjetService extends BaseService implements CategorieProjetServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        CategorieProjetRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CategorieProjetResource::class;
    }
}
