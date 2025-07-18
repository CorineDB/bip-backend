<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Workflows\StoreWorkflowRequest;
use App\Http\Requests\Workflows\UpdateWorkflowRequest;
use App\Services\Contracts\WorkflowServiceInterface;
use Illuminate\Http\JsonResponse;

class WorkflowController extends Controller
{
    protected WorkflowServiceInterface $service;

    public function __construct(WorkflowServiceInterface $service)
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

    public function store(StoreWorkflowRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateWorkflowRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}