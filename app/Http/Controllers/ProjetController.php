<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\projets\StoreProjetRequest;
use App\Http\Requests\projets\UpdateProjetRequest;
use App\Services\Contracts\ProjetServiceInterface;
use Illuminate\Http\JsonResponse;

class ProjetController extends Controller
{
    protected ProjetServiceInterface $service;

    public function __construct(ProjetServiceInterface $service)
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

    public function store(StoreProjetRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateProjetRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Récupérer les projets sélectionnables (en cours de maturation - statut différent de PRET)
     */
    public function projetsEnCoursMaturation(): JsonResponse
    {
        return $this->service->getProjetsEnCoursMaturation();
    }

    /**
     * Récupérer les projets matures (arrivés à maturité - statut = PRET)
     */
    public function projetsArrivesAMaturite(): JsonResponse
    {
        return $this->service->getProjetsArrivesAMaturite();
    }
}