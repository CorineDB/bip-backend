<?php

namespace App\Repositories;

use App\Models\ComposantProgramme;
use App\Repositories\Contracts\ComposantProgrammeRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class ComposantProgrammeRepository extends BaseRepository implements ComposantProgrammeRepositoryInterface
{
    public function __construct(ComposantProgramme $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}