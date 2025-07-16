<?php

namespace App\Repositories;

use App\Models\TypeProgramme;
use App\Repositories\Contracts\TypeProgrammeRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class TypeProgrammeRepository extends BaseRepository implements TypeProgrammeRepositoryInterface
{
    public function __construct(TypeProgramme $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}