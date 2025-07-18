<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use App\Repositories\Contracts\TrackInfoRepositoryInterface;
use App\Services\Contracts\TrackInfoServiceInterface;

class TrackInfoService extends BaseService implements TrackInfoServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected ApiResourceInterface $resource;

    public function __construct(
        TrackInfoRepositoryInterface $repository,
        ApiResourceInterface $resource
    )
    {
        parent::__construct($repository, $resource);
    }
}