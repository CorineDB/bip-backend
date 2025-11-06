<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\idees_projet\FilterIdeeRequest;
use App\Http\Requests\idees_projet\StoreIdeeProjetRequest;
use App\Http\Requests\idees_projet\UpdateIdeeProjetRequest;
use App\Services\Contracts\IdeeProjetServiceInterface;
use Illuminate\Http\JsonResponse;

class IdeeProjetController extends Controller
{
    protected IdeeProjetServiceInterface $service;

    public function __construct(IdeeProjetServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(FilterIdeeRequest $request): JsonResponse
    {
        if ($request->filled('statut')) {
            $statuts = is_array($request->statut) ? $request->statut : [$request->statut];
            return $this->service->filterBy($statuts);
        }
        else{
            return $this->service->all();
        }
    }

    public function dashboard(FilterIdeeRequest $request): JsonResponse
    {
        if ($request->filled('statut')) {
            $statuts = is_array($request->statut) ? $request->statut : [$request->statut];
            return $this->service->filterBy($statuts);
        }
        else{
            return $this->service->dashboard();
        }
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    public function store(StoreIdeeProjetRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateIdeeProjetRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    public function filterByStatut(FilterIdeeRequest $filterRequest): JsonResponse
    {
        return $this->service->filterBy($filterRequest->all());
    }

    public function demandeurs(): JsonResponse
    {
        return $this->service->demandeurs();
    }
}
