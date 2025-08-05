<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface IdeeProjetServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here

    public function filterBy(array $data):JsonResponse;

    public function demandeurs(): JsonResponse;
}
