<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TypesFinancement\StoreTypeFinancementRequest;
use App\FormRequest\TypesFinancement\UpdateTypeFinancementRequest;
use App\Services\Contracts\TypeFinancementServiceInterface;
use Illuminate\Http\JsonResponse;

class TypeFinancementController extends Controller
{
    protected TypeFinancementServiceInterface $service;

    public function __construct(TypeFinancementServiceInterface $service)
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

    public function store(StoreTypeFinancementRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateTypeFinancementRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
