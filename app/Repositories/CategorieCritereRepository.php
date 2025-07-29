<?php

namespace App\Repositories;

use App\Models\CategorieCritere;
use App\Repositories\Contracts\CategorieCritereRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CategorieCritereRepository extends BaseRepository implements CategorieCritereRepositoryInterface
{
    public function __construct(CategorieCritere $model)
    {
        parent::__construct($model);
    }

    /**
     * Find CategorieCritere by type
     */
    public function findByType(string $type)
    {
        return $this->model->where('type', $type)->first();
    }
}