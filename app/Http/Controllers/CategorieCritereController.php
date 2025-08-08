<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\categories_critere\StoreCategorieCritereRequest;
use App\Http\Requests\categories_critere\UpdateCategorieCritereRequest;
use App\Services\Contracts\CategorieCritereServiceInterface;
use Illuminate\Http\JsonResponse;

class CategorieCritereController extends Controller
{
    protected CategorieCritereServiceInterface $service;

    public function __construct(CategorieCritereServiceInterface $service)
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

    public function store(StoreCategorieCritereRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCategorieCritereRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Get the grille evaluation preliminaire des impacts climatique
     */
    public function getGrilleEvaluationPreliminaire(): JsonResponse
    {
        return $this->service->getGrilleEvaluationPreliminaire();
    }

    /**
     * Update the grille evaluation preliminaire des impacts climatique
     */
    public function updateGrilleEvaluationPreliminaire(UpdateCategorieCritereRequest $request): JsonResponse
    {
        return $this->service->updateGrilleEvaluationPreliminaire($request->all());
    }

    /**
     * Get the grille analyse multi-criteres
     */
    public function getGrilleAnalyseMultiCriteres(): JsonResponse
    {
        return $this->service->getGrilleAnalyseMultiCriteres();
    }

    /**
     * Get the grille analyse multi-criteres with evaluations for an idee de projet
     */
    public function getGrilleAnalyseMultiCriteresAvecEvaluations($ideeProjetId): JsonResponse
    {
        return $this->service->getGrilleAnalyseMultiCriteresAvecEvaluations($ideeProjetId);
    }

    /**
     * Update the grille analyse multi-criteres
     */
    public function updateGrilleAnalyseMultiCriteres(UpdateCategorieCritereRequest $request): JsonResponse
    {
        return $this->service->updateGrilleAnalyseMultiCriteres($request->all());
    }
}