<?php

namespace App\Repositories;

use App\Models\CategorieDocument;
use App\Repositories\Contracts\CategorieDocumentRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CategorieDocumentRepository extends BaseRepository implements CategorieDocumentRepositoryInterface
{
    public function __construct(CategorieDocument $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}