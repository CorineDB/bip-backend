<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\types_intervention\StoreTypeInterventionRequest;
use App\Http\Requests\types_intervention\UpdateTypeInterventionRequest;
use App\Services\Contracts\TypeInterventionServiceInterface;
use Illuminate\Http\JsonResponse;

class TypeInterventionController extends Controller
{
    protected TypeInterventionServiceInterface $service;

    public function __construct(TypeInterventionServiceInterface $service)
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

    public function store(StoreTypeInterventionRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateTypeInterventionRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
