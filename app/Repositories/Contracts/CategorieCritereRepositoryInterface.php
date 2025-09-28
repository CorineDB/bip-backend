<?php

namespace App\Repositories\Contracts;

use App\Models\CategorieCritere;
use Illuminate\Database\Eloquent\Model;

interface CategorieCritereRepositoryInterface extends BaseRepositoryInterface
{
    // Define contract methods here

    /**
     * Get the unique canevas d'évaluation climatique
     */
    public function getCanevasEvaluationClimatique(): CategorieCritere|null;

    /**
     * Get the unique canevas d'AMC
     */
    public function getCanevasAMC(): CategorieCritere|null;

    /**
     * Get the unique canevas d'évaluation de pertinence
     */
    public function getCanevasEvaluationDePertinence(): CategorieCritere|null;
}
