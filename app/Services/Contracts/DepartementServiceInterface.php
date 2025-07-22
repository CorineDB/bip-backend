<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface DepartementServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here
    public function communes($idDepartement): JsonResponse;
}