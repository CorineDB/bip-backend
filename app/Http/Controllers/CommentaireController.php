<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\commentaires\StoreCommentaireRequest;
use App\Http\Requests\commentaires\UpdateCommentaireRequest;
use App\Services\Contracts\CommentaireServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;

class CommentaireController extends Controller
{
    protected CommentaireServiceInterface $service;

    public function __construct(CommentaireServiceInterface $service)
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

    public function store(StoreCommentaireRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCommentaireRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Récupérer tous les commentaires d'une ressource spécifique
     *
     * @param string $resourceType - Type de ressource (projet, note, tdr, rapport, etc.)
     * @param $resourceId - ID de la ressource
     * @return JsonResponse
     */
    public function getByResource(string $resourceType, $resourceId): JsonResponse
    {
        return $this->service->getCommentairesParRessource($resourceType, $resourceId);
    }
}
