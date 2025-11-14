<?php

namespace App\Repositories;

use App\Models\Rapport;
use App\Repositories\Eloquent\BaseRepository;
use App\Repositories\Contracts\RapportRepositoryInterface;

class RapportRepository extends BaseRepository implements RapportRepositoryInterface
{
    public function __construct(Rapport $rapport)
    {
        parent::__construct($rapport);
    }
}