<?php

namespace App\Repositories;

use App\Models\Dgpd;
use App\Repositories\Contracts\DgpdRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class DgpdRepository extends BaseRepository implements DgpdRepositoryInterface
{
    public function __construct(Dgpd $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}