<?php

namespace App\Repositories;

use App\Models\EvaluationCritere;
use App\Repositories\Contracts\EvaluationCritereRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class EvaluationCritereRepository extends BaseRepository implements EvaluationCritereRepositoryInterface
{
    public function __construct(EvaluationCritere $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}