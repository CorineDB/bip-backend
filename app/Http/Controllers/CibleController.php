<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\cibles\StoreCibleRequest;
use App\Http\Requests\cibles\UpdateCibleRequest;
use App\Services\Contracts\CibleServiceInterface;
use Illuminate\Http\JsonResponse;

class CibleController extends Controller
{
    protected CibleServiceInterface $service;

    public function __construct(CibleServiceInterface $service)
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

    public function store(StoreCibleRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCibleRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}