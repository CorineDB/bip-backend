<?php

namespace App\Repositories;

use App\Models\Commune;
use App\Repositories\Contracts\CommuneRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CommuneRepository extends BaseRepository implements CommuneRepositoryInterface
{
    public function __construct(Commune $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}