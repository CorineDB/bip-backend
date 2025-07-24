<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface OrganisationServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here
    public function ministeres(): JsonResponse;

    public function organismes_de_tutelle($idMinistere): JsonResponse;
}