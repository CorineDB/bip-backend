<?php

namespace App\Repositories;

use App\Models\StoreUserRequest;
use App\Repositories\Contracts\StoreUserRequestRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class StoreUserRequestRepository extends BaseRepository implements StoreUserRequestRepositoryInterface
{
    public function __construct(StoreUserRequest $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}