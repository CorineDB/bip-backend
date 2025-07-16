<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Villages\StoreVillageRequest;
use App\FormRequest\Villages\UpdateVillageRequest;
use App\Services\Contracts\VillageServiceInterface;
use Illuminate\Http\JsonResponse;

class VillageController extends Controller
{
    protected VillageServiceInterface $service;

    public function __construct(VillageServiceInterface $service)
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

    public function store(StoreVillageRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateVillageRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}