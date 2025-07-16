<?php

namespace App\Repositories;

use App\Models\Projet;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class ProjetRepository extends BaseRepository implements ProjetRepositoryInterface
{
    public function __construct(Projet $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}