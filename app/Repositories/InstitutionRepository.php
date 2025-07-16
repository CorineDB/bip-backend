<?php

namespace App\Repositories;

use App\Models\Institution;
use App\Repositories\Contracts\InstitutionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class InstitutionRepository extends BaseRepository implements InstitutionRepositoryInterface
{
    public function __construct(Institution $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}