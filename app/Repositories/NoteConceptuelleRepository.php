<?php

namespace App\Repositories;

use App\Models\NoteConceptuelle;
use App\Repositories\Contracts\NoteConceptuelleRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class NoteConceptuelleRepository extends BaseRepository implements NoteConceptuelleRepositoryInterface
{
    public function __construct(NoteConceptuelle $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}