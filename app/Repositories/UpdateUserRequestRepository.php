<?php

namespace App\Repositories;

use App\Models\UpdateUserRequest;
use App\Repositories\Contracts\UpdateUserRequestRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class UpdateUserRequestRepository extends BaseRepository implements UpdateUserRequestRepositoryInterface
{
    public function __construct(UpdateUserRequest $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}