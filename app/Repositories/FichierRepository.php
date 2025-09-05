<?php

namespace App\Repositories;

use App\Models\Fichier;
use App\Repositories\Contracts\FichierRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class FichierRepository extends BaseRepository implements FichierRepositoryInterface
{
    public function __construct(Fichier $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}