<?php

namespace App\Repositories;

use App\Models\ODD;
use App\Repositories\Contracts\ODDRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class ODDRepository extends BaseRepository implements ODDRepositoryInterface
{
    public function __construct(ODD $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}