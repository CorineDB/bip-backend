<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface DpafServiceInterface extends AbstractServiceInterface
{
    /**
     * Change the admin account for DPAF.
     *
     * @param array $userData
     * @return JsonResponse
     */
    //public function changeAdminAccount(array $userData): JsonResponse;
}