<?php

namespace App\Repositories\Contracts;

interface DocumentRepositoryInterface extends BaseRepositoryInterface
{
    // Define contract methods here

    /**
     * Get the unique fiche idée
     */
    public function getFicheIdee();
}