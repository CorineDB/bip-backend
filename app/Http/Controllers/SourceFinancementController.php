<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\SourcesFinancement\StoreSourceFinancementRequest;
use App\FormRequest\SourcesFinancement\UpdateSourceFinancementRequest;
use App\Services\Contracts\SourceFinancementServiceInterface;
use Illuminate\Http\JsonResponse;

class SourceFinancementController extends Controller
{
    protected SourceFinancementServiceInterface $service;

    public function __construct(SourceFinancementServiceInterface $service)
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

    public function store(StoreSourceFinancementRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateSourceFinancementRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
