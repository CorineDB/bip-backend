<?php

namespace App\Repositories;

use App\Models\Canevas;
use App\Repositories\Contracts\CanevasRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CanevasRepository extends BaseRepository implements CanevasRepositoryInterface
{
    public function __construct(Canevas $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}
