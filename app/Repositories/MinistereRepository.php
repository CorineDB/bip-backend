<?php

namespace App\Repositories;

use App\Models\Ministere;
use App\Repositories\Contracts\MinistereRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class MinistereRepository extends BaseRepository implements MinistereRepositoryInterface
{
    public function __construct(Ministere $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}