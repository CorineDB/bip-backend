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
}