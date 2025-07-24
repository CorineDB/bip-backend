<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\composants_programme\StoreComposantProgrammeRequest;
use App\Http\Requests\composants_programme\UpdateComposantProgrammeRequest;
use App\Services\Contracts\ComposantProgrammeServiceInterface;
use Illuminate\Http\JsonResponse;

class ComposantProgrammeController extends Controller
{
    protected ComposantProgrammeServiceInterface $service;

    public function __construct(ComposantProgrammeServiceInterface $service)
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

    public function store(StoreComposantProgrammeRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateComposantProgrammeRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    public function axesPag(): JsonResponse
    {
        return $this->service->axes_pag();
    }

    public function piliersPag(): JsonResponse
    {
        return $this->service->piliers_pag();
    }

    public function actionsPag(): JsonResponse
    {
        return $this->service->actions_pag();
    }

    public function orientationsStrategiquesPnd(): JsonResponse
    {
        return $this->service->orientations_strategiques_pnd();
    }

    public function objectifsStrategiquesPnd(): JsonResponse
    {
        return $this->service->objectifs_strategiques_pnd();
    }

    public function resultatsStrategiquesPnd(): JsonResponse
    {
        return $this->service->resultats_strategiques_pnd();
    }
}
