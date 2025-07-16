<?php

namespace App\Repositories;

use App\Models\Workflow;
use App\Repositories\Contracts\WorkflowRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class WorkflowRepository extends BaseRepository implements WorkflowRepositoryInterface
{
    public function __construct(Workflow $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}