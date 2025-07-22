<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface ArrondissementServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here
    public function villages($idArrondissement): JsonResponse;
}