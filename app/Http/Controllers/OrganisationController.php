<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\organisations\StoreOrganisationRequest;
use App\Http\Requests\organisations\UpdateOrganisationRequest;
use App\Services\Contracts\OrganisationServiceInterface;
use Illuminate\Http\JsonResponse;

class OrganisationController extends Controller
{
    protected OrganisationServiceInterface $service;

    public function __construct(OrganisationServiceInterface $service)
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

    public function store(StoreOrganisationRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateOrganisationRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    public function ministeres(): JsonResponse{
        return $this->service->ministeres();
    }

    public function organismes_de_tutelle($idMinistere): JsonResponse{
        return $this->service->organismes_de_tutelle($idMinistere);
    }
}