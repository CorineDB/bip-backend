<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\fichiers\StoreFichierRequest;
use App\Http\Requests\fichiers\UpdateFichierRequest;
use App\Services\Contracts\FichierServiceInterface;
use Illuminate\Http\JsonResponse;

class FichierController extends Controller
{
    protected FichierServiceInterface $service;

    public function __construct(FichierServiceInterface $service)
    {
        $this->middleware('auth:api');
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

    public function store(StoreFichierRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateFichierRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Upload d'un fichier libre (sans ressource)
     */
    public function upload(Request $request): JsonResponse
    {
        return $this->service->uploadFichierLibre($request);
    }

    /**
     * Télécharger un fichier
     */
    public function telecharger($id)
    {
        return $this->service->telechargerFichier($id);
    }

    /**
     * Visualiser un fichier
     */
    public function view($id)
    {
        return $this->service->visualiserFichier($id);
    }

    /**
     * Partager un fichier avec d'autres utilisateurs
     */
    public function partager(Request $request, $id): JsonResponse
    {
        return $this->service->partagerFichier($id, $request->all());
    }

    /**
     * Supprimer un fichier uploadé librement
     */
    public function supprimer($id): JsonResponse
    {
        return $this->service->supprimerFichier($id);
    }

    /**
     * Visualiser un fichier par hash d'accès
     */
    public function visualiserFichierParHash(string $hash)
    {
        return $this->service->visualiserFichier($hash);
    }

    /**
     * Télécharger un fichier par hash d'accès
     */
    public function telechargerFichierParHash(string $hash)
    {
        return $this->service->telechargerFichier($hash);
    }
}