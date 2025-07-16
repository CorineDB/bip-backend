<?php

namespace App\Repositories;

use App\Models\Departement;
use App\Repositories\Contracts\DepartementRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class DepartementRepository extends BaseRepository implements DepartementRepositoryInterface
{
    public function __construct(Departement $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}