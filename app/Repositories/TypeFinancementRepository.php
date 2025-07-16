<?php

namespace App\Repositories;

use App\Models\TypeFinancement;
use App\Repositories\Contracts\TypeFinancementRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class TypeFinancementRepository extends BaseRepository implements TypeFinancementRepositoryInterface
{
    public function __construct(TypeFinancement $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}