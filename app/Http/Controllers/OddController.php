<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\odds\StoreOddRequest;
use App\Http\Requests\odds\UpdateOddRequest;
use App\Services\Contracts\OddServiceInterface;
use Illuminate\Http\JsonResponse;

class OddController extends Controller
{
    protected OddServiceInterface $service;

    public function __construct(OddServiceInterface $service)
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

    public function store(StoreOddRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateOddRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}