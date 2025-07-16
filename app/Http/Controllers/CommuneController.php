<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Communes\StoreCommuneRequest;
use App\FormRequest\Communes\UpdateCommuneRequest;
use App\Services\Contracts\CommuneServiceInterface;
use Illuminate\Http\JsonResponse;

class CommuneController extends Controller
{
    protected CommuneServiceInterface $service;

    public function __construct(CommuneServiceInterface $service)
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

    public function store(StoreCommuneRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCommuneRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}