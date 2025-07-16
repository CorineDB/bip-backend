<?php

namespace App\Repositories;

use App\Models\Secteur;
use App\Repositories\Contracts\SecteurRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class SecteurRepository extends BaseRepository implements SecteurRepositoryInterface
{
    public function __construct(Secteur $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}