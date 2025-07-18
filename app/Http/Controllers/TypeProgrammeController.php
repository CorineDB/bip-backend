<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\types_programme\StoreTypeProgrammeRequest;
use App\Http\Requests\types_programme\UpdateTypeProgrammeRequest;
use App\Services\Contracts\TypeProgrammeServiceInterface;
use Illuminate\Http\JsonResponse;

class TypeProgrammeController extends Controller
{
    protected TypeProgrammeServiceInterface $service;

    public function __construct(TypeProgrammeServiceInterface $service)
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

    public function store(StoreTypeProgrammeRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateTypeProgrammeRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
