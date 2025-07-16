<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\CategoriesCanevas\StoreCategorieCanevasRequest;
use App\FormRequest\CategoriesCanevas\UpdateCategorieCanevasRequest;
use App\Services\Contracts\CategorieCanevasServiceInterface;
use Illuminate\Http\JsonResponse;

class CategorieCanevasController extends Controller
{
    protected CategorieCanevasServiceInterface $service;

    public function __construct(CategorieCanevasServiceInterface $service)
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

    public function store(StoreCategorieCanevasRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCategorieCanevasRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}
