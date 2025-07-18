<?php

namespace App\Repositories;

use App\Models\Odd;
use App\Repositories\Contracts\OddRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class OddRepository extends BaseRepository implements OddRepositoryInterface
{
    public function __construct(Odd $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}
