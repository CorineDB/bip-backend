<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\financements\StoreFinancementRequest;
use App\Http\Requests\financements\UpdateFinancementRequest;
use App\Services\Contracts\FinancementServiceInterface;
use Illuminate\Http\JsonResponse;

class FinancementController extends Controller
{
    protected FinancementServiceInterface $service;

    public function __construct(FinancementServiceInterface $service)
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

    public function store(StoreFinancementRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateFinancementRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}