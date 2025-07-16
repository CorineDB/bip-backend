<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\ODDs\StoreODDRequest;
use App\FormRequest\ODDs\UpdateODDRequest;
use App\Services\Contracts\ODDServiceInterface;
use Illuminate\Http\JsonResponse;

class ODDController extends Controller
{
    protected ODDServiceInterface $service;

    public function __construct(ODDServiceInterface $service)
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

    public function store(StoreODDRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateODDRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}