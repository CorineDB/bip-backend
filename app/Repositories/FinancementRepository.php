<?php

namespace App\Repositories;

use App\Models\Financement;
use App\Repositories\Contracts\FinancementRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class FinancementRepository extends BaseRepository implements FinancementRepositoryInterface
{
    public function __construct(Financement $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}