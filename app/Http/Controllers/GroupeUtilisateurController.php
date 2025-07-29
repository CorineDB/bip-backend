<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\groupes_utilisateur\StoreGroupeUtilisateurRequest;
use App\Http\Requests\groupes_utilisateur\UpdateGroupeUtilisateurRequest;
use App\Services\Contracts\GroupeUtilisateurServiceInterface;
use Illuminate\Http\JsonResponse;

class GroupeUtilisateurController extends Controller
{
    protected GroupeUtilisateurServiceInterface $service;

    public function __construct(GroupeUtilisateurServiceInterface $service)
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

    public function store(StoreGroupeUtilisateurRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateGroupeUtilisateurRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}