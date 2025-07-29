<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\dgpd\StoreDgpdRequest;
use App\Http\Requests\dgpd\UpdateDgpdRequest;
use App\Services\Contracts\DgpdServiceInterface;
use Illuminate\Http\JsonResponse;

class DgpdController extends Controller
{
    protected DgpdServiceInterface $service;

    public function __construct(DgpdServiceInterface $service)
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

    public function store(StoreDgpdRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateDgpdRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}