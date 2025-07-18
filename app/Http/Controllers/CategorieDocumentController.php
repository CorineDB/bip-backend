<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\categories_document\StoreCategorieDocumentRequest;
use App\Http\Requests\categories_document\UpdateCategorieDocumentRequest;
use App\Services\Contracts\CategorieDocumentServiceInterface;
use Illuminate\Http\JsonResponse;

class CategorieDocumentController extends Controller
{
    protected CategorieDocumentServiceInterface $service;

    public function __construct(CategorieDocumentServiceInterface $service)
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

    public function store(StoreCategorieDocumentRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCategorieDocumentRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}