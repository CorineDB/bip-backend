<?php

namespace App\Repositories;

use App\Models\Organisation;
use App\Repositories\Contracts\OrganisationRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class OrganisationRepository extends BaseRepository implements OrganisationRepositoryInterface
{
    public function __construct(Organisation $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}