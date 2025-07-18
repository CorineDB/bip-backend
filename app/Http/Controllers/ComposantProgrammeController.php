<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\composants_programme\StoreComposantProgrammeRequest;
use App\Http\Requests\composants_programme\UpdateComposantProgrammeRequest;
use App\Services\Contracts\ComposantProgrammeServiceInterface;
use Illuminate\Http\JsonResponse;

class ComposantProgrammeController extends Controller
{
    protected ComposantProgrammeServiceInterface $service;

    public function __construct(ComposantProgrammeServiceInterface $service)
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

    public function store(StoreComposantProgrammeRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateComposantProgrammeRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
