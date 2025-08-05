<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Services\Contracts\AbstractServiceInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

abstract class BaseService implements AbstractServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected string $resourceClass;

    public function __construct(
        BaseRepositoryInterface $repository
    ) {
        $this->repository = $repository;
        $this->resourceClass = $this->getResourceClass();
    }

    /**
     * Get the resource class for this service
     */
    abstract protected function getResourceClass(): string;

    public function all(): JsonResponse
    {
        try {
            $data = $this->repository->all();
            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function find(int|string $id): JsonResponse
    {
        try {
            $item = $this->repository->findOrFail($id);
            return (new $this->resourceClass($item))->response();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function create(array $data): JsonResponse
    {
        try {
            $item = $this->repository->create($data);
            return (new $this->resourceClass($item))
                ->additional(['message' => 'Resource created successfully.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function update(int|string $id, array $data): JsonResponse
    {
        try {
            $updated = $this->repository->update($id, $data);
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found or not updated.',
                ], 404);
            }

            $item = $this->repository->findOrFail($id);

            return (new $this->resourceClass($item))
                ->additional(['message' => 'Resource updated successfully.'])
                ->response();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function delete(int|string $id): JsonResponse
    {
        try {
            $deleted = $this->repository->delete($id);
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found or not deleted.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Resource deleted successfully.',
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    protected function errorResponse(Exception $e): JsonResponse
    {        // Tu peux logger l'erreur ici si tu veux
        return response()->json([
            'success' => false,
            'message' => 'An error occurred.',
            'error' => $e->getMessage(),
        ], 500);
    }
}