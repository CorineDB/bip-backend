<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Institutions\StoreInstitutionRequest;
use App\FormRequest\Institutions\UpdateInstitutionRequest;
use App\Services\Contracts\InstitutionServiceInterface;
use Illuminate\Http\JsonResponse;

class InstitutionController extends Controller
{
    protected InstitutionServiceInterface $service;

    public function __construct(InstitutionServiceInterface $service)
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

    public function store(StoreInstitutionRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateInstitutionRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}