<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Ministeres\StoreMinistereRequest;
use App\FormRequest\Ministeres\UpdateMinistereRequest;
use App\Services\Contracts\MinistereServiceInterface;
use Illuminate\Http\JsonResponse;

class MinistereController extends Controller
{
    protected MinistereServiceInterface $service;

    public function __construct(MinistereServiceInterface $service)
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

    public function store(StoreMinistereRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateMinistereRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}