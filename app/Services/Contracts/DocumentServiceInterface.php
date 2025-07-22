<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface DocumentServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here
    public function ficheIdee(): JsonResponse;

    public function createOrUpdateFicheIdee(array $data): JsonResponse;
}