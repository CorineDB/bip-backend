<?php

namespace App\Repositories;

use App\Models\CategorieProjet;
use App\Repositories\Contracts\CategorieProjetRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CategorieProjetRepository extends BaseRepository implements CategorieProjetRepositoryInterface
{
    public function __construct(CategorieProjet $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}