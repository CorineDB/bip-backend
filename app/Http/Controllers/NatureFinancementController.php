<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\NaturesFinancement\StoreNatureFinancementRequest;
use App\FormRequest\NaturesFinancement\UpdateNatureFinancementRequest;
use App\Services\Contracts\NatureFinancementServiceInterface;
use Illuminate\Http\JsonResponse;

class NatureFinancementController extends Controller
{
    protected NatureFinancementServiceInterface $service;

    public function __construct(NatureFinancementServiceInterface $service)
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

    public function store(StoreNatureFinancementRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateNatureFinancementRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
