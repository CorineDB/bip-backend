<?php

namespace App\Repositories;

use App\Models\Village;
use App\Repositories\Contracts\VillageRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class VillageRepository extends BaseRepository implements VillageRepositoryInterface
{
    public function __construct(Village $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}