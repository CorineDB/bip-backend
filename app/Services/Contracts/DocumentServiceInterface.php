<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface DocumentServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here

    public function createFicheIdee(array $data): JsonResponse;

    //public function modifierFicheIdee(array $data): JsonResponse;
}