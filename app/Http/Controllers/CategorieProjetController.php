<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CategoriesProjet\StoreCategorieProjetRequest;
use App\Http\Requests\CategoriesProjet\UpdateCategorieProjetRequest;
use App\Services\Contracts\CategorieProjetServiceInterface;
use Illuminate\Http\JsonResponse;

class CategorieProjetController extends Controller
{
    protected CategorieProjetServiceInterface $service;

    public function __construct(CategorieProjetServiceInterface $service)
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

    public function store(StoreCategorieProjetRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCategorieProjetRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
