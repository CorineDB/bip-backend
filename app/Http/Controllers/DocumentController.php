<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\documents\StoreDocumentRequest;
use App\Http\Requests\documents\UpdateDocumentRequest;
use App\Services\Contracts\DocumentServiceInterface;
use Illuminate\Http\JsonResponse;

class DocumentController extends Controller
{
    protected DocumentServiceInterface $service;

    public function __construct(DocumentServiceInterface $service)
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

    public function store(StoreDocumentRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateDocumentRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Fiche idees
     */

    public function create_fiche_idee(StoreDocumentRequest $request): JsonResponse
    {
        return $this->service->createFicheIdee($request->all());
    }
}