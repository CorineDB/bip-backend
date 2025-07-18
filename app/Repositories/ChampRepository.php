<?php

namespace App\Repositories;

use App\Models\Champ;
use App\Repositories\Contracts\ChampRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class ChampRepository extends BaseRepository implements ChampRepositoryInterface
{
    public function __construct(Champ $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}