<?php

namespace App\Repositories;

use App\Models\Commentaire;
use App\Repositories\Contracts\CommentaireRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CommentaireRepository extends BaseRepository implements CommentaireRepositoryInterface
{
    public function __construct(Commentaire $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}