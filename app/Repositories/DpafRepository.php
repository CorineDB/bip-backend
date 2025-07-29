<?php

namespace App\Repositories;

use App\Models\Dpaf;
use App\Repositories\Contracts\DpafRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class DpafRepository extends BaseRepository implements DpafRepositoryInterface
{
    public function __construct(Dpaf $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}