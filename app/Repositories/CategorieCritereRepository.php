<?php

namespace App\Repositories;

use App\Models\CategorieCritere;
use App\Repositories\Contracts\CategorieCritereRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;

class CategorieCritereRepository extends BaseRepository implements CategorieCritereRepositoryInterface
{
    public function __construct(CategorieCritere $model)
    {
        parent::__construct($model);
    }

    /**
     * Find CategorieCritere by type
     */
    public function findByType(string $type)
    {
        return $this->model->where('type', $type)->first();
    }

    public function getCanevasEvaluationClimatique(): CategorieCritere|null
    {
        $grille = $this->findByAttribute('slug', 'evaluation-preliminaire-multi-projet-impact-climatique');

        return $grille ? ($grille->load(['criteres.notations', 'fichiers'])) : null;
    }

    public function getCanevasAMC(): CategorieCritere|null
    {
        $grille = $this->findByAttribute('slug', 'grille-analyse-multi-critere');

        return $grille ? ($grille->load(['criteres.notations', 'notations', 'fichiers'])) : null;
    }

    public function getCanevasEvaluationDePertinence(): CategorieCritere|null
    {
        $grille = $this->findByAttribute('slug', 'grille-evaluation-pertinence-idee-projet');

        return $grille ? ($grille->load(['criteres.notations', 'fichiers'])) : null;
    }
}
