<?php

namespace App\Repositories;

use App\Models\SourceFinancement;
use App\Repositories\Contracts\SourceFinancementRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class SourceFinancementRepository extends BaseRepository implements SourceFinancementRepositoryInterface
{
    public function __construct(SourceFinancement $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}