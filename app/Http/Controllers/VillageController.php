<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\villages\UpdateVillageRequest;
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

    public function update(UpdateVillageRequest $request, $id): JsonResponse
    {
        dd($request->all());
        return $this->service->update($id, $request->validate());
    }
}
