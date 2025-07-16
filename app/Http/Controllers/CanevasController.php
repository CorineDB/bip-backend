<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Canevas\StoreCanevaRequest;
use App\FormRequest\Canevas\UpdateCanevaRequest;
use App\Services\Contracts\CanevasServiceInterface;
use Illuminate\Http\JsonResponse;

class CanevasController extends Controller
{
    protected CanevasServiceInterface $service;

    public function __construct(CanevasServiceInterface $service)
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

    public function store(StoreCanevaRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCanevaRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}