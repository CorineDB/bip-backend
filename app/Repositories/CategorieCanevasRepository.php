<?php

namespace App\Repositories;

use App\Models\CategorieCaneva;
use App\Repositories\Contracts\CategorieCanevasRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CategorieCanevasRepository extends BaseRepository implements CategorieCanevasRepositoryInterface
{
    public function __construct(CategorieCaneva $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}
