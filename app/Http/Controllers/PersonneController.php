<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Personnes\StorePersonneRequest;
use App\Http\Requests\Personnes\UpdatePersonneRequest;
use App\Services\Contracts\PersonneServiceInterface;
use Illuminate\Http\JsonResponse;

class PersonneController extends Controller
{
    protected PersonneServiceInterface $service;

    public function __construct(PersonneServiceInterface $service)
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

    public function store(StorePersonneRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdatePersonneRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}