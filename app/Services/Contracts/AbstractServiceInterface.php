<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface AbstractServiceInterface
{
    /**
     * Get all records.
     *
     * @return JsonResponse
     */
    public function all(): JsonResponse;

    /**
     * Find a record by ID.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function find(int|string $id): JsonResponse;

    /**
     * Create a new record.
     *
     * @param array $data
     * @return JsonResponse
     */
    public function create(array $data): JsonResponse;

    /**
     * Update a record by ID.
     *
     * @param int|string $id
     * @param array $data
     * @return JsonResponse
     */
    public function update(int|string $id, array $data): JsonResponse;

    /**
     * Delete a record by ID.
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function delete(int|string $id): JsonResponse;
}