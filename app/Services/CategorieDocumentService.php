<?php

namespace App\Services;

use App\Http\Resources\CategorieDocumentResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CategorieDocumentRepositoryInterface;
use App\Services\Contracts\CategorieDocumentServiceInterface;

class CategorieDocumentService extends BaseService implements CategorieDocumentServiceInterface
{
    protected BaseRepositoryInterface $repository;
    public function __construct(
        CategorieDocumentRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CategorieDocumentResource::class;
    }
}