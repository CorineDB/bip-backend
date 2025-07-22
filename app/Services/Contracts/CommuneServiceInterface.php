<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface CommuneServiceInterface extends AbstractServiceInterface
{
    // Define contract methods
    public function arrondissements($idCommune): JsonResponse;
}