<?php

namespace App\Repositories;

use App\Models\Decision;
use App\Repositories\Contracts\DecisionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class DecisionRepository extends BaseRepository implements DecisionRepositoryInterface
{
    public function __construct(Decision $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}