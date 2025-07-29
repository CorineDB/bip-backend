<?php

namespace App\Repositories;

use App\Models\GroupeUtilisateur;
use App\Repositories\Contracts\GroupeUtilisateurRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class GroupeUtilisateurRepository extends BaseRepository implements GroupeUtilisateurRepositoryInterface
{
    public function __construct(GroupeUtilisateur $model)
    {
        parent::__construct($model);
    }

    // Add custom methods here
}