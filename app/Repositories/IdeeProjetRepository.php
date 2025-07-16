<?php

namespace App\Repositories;

use App\Models\IdeeProjet;
use App\Repositories\Contracts\IdeeProjetRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class IdeeProjetRepository extends BaseRepository implements IdeeProjetRepositoryInterface
{
    public function __construct(IdeeProjet $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}