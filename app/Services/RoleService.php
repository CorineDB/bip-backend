<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Services\Contracts\RoleServiceInterface;
use App\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class RoleService extends BaseService implements RoleServiceInterface
{
    public function __construct(RoleRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return RoleResource::class;
    }
    
    /**
     * Get role permissions records.
     *
     * @return JsonResponse
     */
    public function permissions(int|string $id): JsonResponse{

        try {

            $item = $this->repository->findOrFail($id);

            return (new $this->resourceClass($item->load('permissions')))->response();

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Create a new role with permissions.
     *
     * @param array $data
     * @return JsonResponse
     */
    public function create(array $data): JsonResponse
    {
        try {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $item = $this->repository->create($data);
            
            if (!empty($permissions)) {
                $item->permissions()->sync($permissions);
            }

            return (new $this->resourceClass($item->load('permissions')))
                ->additional(['message' => 'Role created successfully.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Update a role with permissions.
     *
     * @param int|string $id
     * @param array $data
     * @return JsonResponse
     */
    public function update(int|string $id, array $data): JsonResponse
    {
        try {
            $permissions = $data['permissions'] ?? [];
            unset($data['permissions']);

            $updated = $this->repository->update($id, $data);
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Role not found or not updated.',
                ], 404);
            }

            $item = $this->repository->findOrFail($id);
            
            if (!empty($permissions)) {
                $item->permissions()->sync($permissions);
            }

            return (new $this->resourceClass($item->load('permissions')))
                ->additional(['message' => 'Role updated successfully.'])
                ->response();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}