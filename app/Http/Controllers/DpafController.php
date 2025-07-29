<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\dpaf\StoreDpafRequest;
use App\Http\Requests\dpaf\UpdateDpafRequest;
use App\Services\Contracts\DpafServiceInterface;
use Illuminate\Http\JsonResponse;

class DpafController extends Controller
{
    protected DpafServiceInterface $service;

    public function __construct(DpafServiceInterface $service)
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

    public function store(StoreDpafRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateDpafRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}