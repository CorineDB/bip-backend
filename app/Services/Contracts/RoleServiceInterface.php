<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface RoleServiceInterface extends AbstractServiceInterface
{
    /**
     * Get role permissions records.
     *
     * @return JsonResponse
     */
    public function permissions(int|string $id): JsonResponse;
}