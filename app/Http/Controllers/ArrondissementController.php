<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Arrondissements\StoreArrondissementRequest;
use App\FormRequest\Arrondissements\UpdateArrondissementRequest;
use App\Services\Contracts\ArrondissementServiceInterface;
use Illuminate\Http\JsonResponse;

class ArrondissementController extends Controller
{
    protected ArrondissementServiceInterface $service;

    public function __construct(ArrondissementServiceInterface $service)
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

    public function store(StoreArrondissementRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateArrondissementRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}