<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface FinancementServiceInterface extends AbstractServiceInterface
{
    // Define contract methods here
    public function types_de_financement(): JsonResponse;

    public function natures_de_financement(): JsonResponse;

    public function natures_type_de_financement($idType): JsonResponse;

    public function sources_de_financement(): JsonResponse;

    public function sources_nature_de_financement($idNature): JsonResponse;
}