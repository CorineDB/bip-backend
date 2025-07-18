<?php

namespace App\Repositories;

use App\Models\Evaluation;
use App\Repositories\Contracts\EvaluationRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class EvaluationRepository extends BaseRepository implements EvaluationRepositoryInterface
{
    public function __construct(Evaluation $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}