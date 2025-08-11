<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface TypeProgrammeServiceInterface extends AbstractServiceInterface
{
    public function programmes(): JsonResponse;
    public function composants_de_programme($idProgramme): JsonResponse;
}
