<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\roles\StoreRoleRequest;
use App\Http\Requests\roles\UpdateRoleRequest;
use App\Services\Contracts\RoleServiceInterface;
use Illuminate\Http\JsonResponse;

class RoleController extends Controller
{
    protected RoleServiceInterface $service;

    public function __construct(RoleServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateRoleRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Get all permissions for a specific role
     */
    public function getPermissions($id): JsonResponse
    {
        return $this->service->permissions($id);
    }

}
