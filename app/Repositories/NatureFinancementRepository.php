<?php

namespace App\Repositories;

use App\Models\NatureFinancement;
use App\Repositories\Contracts\NatureFinancementRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class NatureFinancementRepository extends BaseRepository implements NatureFinancementRepositoryInterface
{
    public function __construct(NatureFinancement $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}