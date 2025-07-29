<?php

namespace App\Repositories;

use App\Models\Notation;
use App\Repositories\Contracts\NotationRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class NotationRepository extends BaseRepository implements NotationRepositoryInterface
{
    public function __construct(Notation $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}