<?php

namespace App\Repositories;

use App\Models\Arrondissement;
use App\Repositories\Contracts\ArrondissementRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class ArrondissementRepository extends BaseRepository implements ArrondissementRepositoryInterface
{
    public function __construct(Arrondissement $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}