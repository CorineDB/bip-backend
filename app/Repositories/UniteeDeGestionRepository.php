<?php

namespace App\Repositories;

use App\Models\UniteeDeGestion;
use App\Repositories\Contracts\UniteeDeGestionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class UniteeDeGestionRepository extends BaseRepository implements UniteeDeGestionRepositoryInterface
{
    public function __construct(UniteeDeGestion $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}