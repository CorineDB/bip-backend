<?php

namespace App\Repositories;

use App\Models\Personne;
use App\Repositories\Contracts\PersonneRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class PersonneRepository extends BaseRepository implements PersonneRepositoryInterface
{
    public function __construct(Personne $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}