<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\secteurs\StoreSecteurRequest;
use App\Http\Requests\secteurs\UpdateSecteurRequest;
use App\Services\Contracts\SecteurServiceInterface;
use Illuminate\Http\JsonResponse;

class SecteurController extends Controller
{
    protected SecteurServiceInterface $service;

    public function __construct(SecteurServiceInterface $service)
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

    public function store(StoreSecteurRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateSecteurRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    public function all_secteurs(): JsonResponse
    {
        return $this->service->all_secteurs();
    }

    public function grands_secteurs(): JsonResponse
    {
        return $this->service->grands_secteurs();
    }

    public function secteurs_grand_secteur($id): JsonResponse
    {
        return $this->service->secteurs_grand_secteur($id);
    }

    public function secteurs(): JsonResponse
    {
        return $this->service->secteurs();
    }

    public function sous_secteurs_secteur($id): JsonResponse
    {
        return $this->service->sous_secteurs_secteur($id);
    }

    public function sous_secteurs(): JsonResponse
    {
        return $this->service->sous_secteurs();
    }

}