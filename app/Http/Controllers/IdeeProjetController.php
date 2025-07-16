<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\idees_projet\StoreIdeeProjetRequest;
use App\FormRequest\idees_projet\UpdateIdeeProjetRequest;
use App\Services\Contracts\idees_projeterviceInterface;
use Illuminate\Http\JsonResponse;

class IdeeProjetController extends Controller
{
    protected idees_projeterviceInterface $service;

    public function __construct(idees_projeterviceInterface $service)
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
}
