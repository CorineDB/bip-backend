<?php

namespace App\Repositories;

use App\Models\Cible;
use App\Repositories\Contracts\CibleRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CibleRepository extends BaseRepository implements CibleRepositoryInterface
{
    public function __construct(Cible $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}