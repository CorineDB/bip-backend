<?php

namespace App\Repositories;

use App\Models\TypeIntervention;
use App\Repositories\Contracts\TypeInterventionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class TypeInterventionRepository extends BaseRepository implements TypeInterventionRepositoryInterface
{
    public function __construct(TypeIntervention $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}