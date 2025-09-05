<?php

namespace App\Services;

use App\Http\Resources\CommentaireResource;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CommentaireRepositoryInterface;
use App\Services\Contracts\CommentaireServiceInterface;

class CommentaireService extends BaseService implements CommentaireServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected CommentaireRepositoryInterface $commentaireRepository;

    public function __construct(
        CommentaireRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
        $this->commentaireRepository = $repository;
    }

    protected function getResourceClass(): string
    {
        return CommentaireResource::class;
    }

    public function update(int|string $id, array $data): JsonResponse
    {
        
    }
}