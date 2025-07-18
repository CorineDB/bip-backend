<?php

namespace App\Repositories;

use App\Models\TrackInfo;
use App\Repositories\Contracts\TrackInfoRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class TrackInfoRepository extends BaseRepository implements TrackInfoRepositoryInterface
{
    public function __construct(TrackInfo $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}