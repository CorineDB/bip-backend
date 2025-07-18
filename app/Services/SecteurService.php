<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\SecteurResource;
use App\Repositories\Contracts\SecteurRepositoryInterface;
use App\Services\Contracts\SecteurServiceInterface;

class SecteurService extends BaseService implements SecteurServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        SecteurRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return SecteurResource::class;
    }
}