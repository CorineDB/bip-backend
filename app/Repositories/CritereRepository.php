<?php

namespace App\Repositories;

use App\Models\Critere;
use App\Repositories\Contracts\CritereRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CritereRepository extends BaseRepository implements CritereRepositoryInterface
{
    public function __construct(Critere $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}