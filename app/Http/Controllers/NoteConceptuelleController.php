<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\notes_conceptuelle\StoreNoteConceptuelleRequest;
use App\Http\Requests\notes_conceptuelle\UpdateNoteConceptuelleRequest;
use App\Services\Contracts\NoteConceptuelleServiceInterface;
use Illuminate\Http\JsonResponse;

class NoteConceptuelleController extends Controller
{
    protected NoteConceptuelleServiceInterface $service;

    public function __construct(NoteConceptuelleServiceInterface $service)
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

    public function store(StoreNoteConceptuelleRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateNoteConceptuelleRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }
}