<?php

namespace App\Services;

use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Http\Resources\DocumentResource;
use App\Http\Resources\NoteConceptuelleResource;
use App\Models\NoteConceptuelle;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\NoteConceptuelleRepositoryInterface;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Services\Contracts\NoteConceptuelleServiceInterface;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class NoteConceptuelleService extends BaseService implements NoteConceptuelleServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected DocumentRepositoryInterface $documentRepository;
    protected ProjetRepositoryInterface $projetRepository;

    public function __construct(
        NoteConceptuelleRepositoryInterface $repository, DocumentRepositoryInterface $documentRepository, ProjetRepositoryInterface $projetRepository
    )
    {
        parent::__construct($repository);

        $this->documentRepository = $documentRepository;
        $this->projetRepository = $projetRepository;
    }

    protected function getResourceClass(): string
    {
        return NoteConceptuelleResource::class;
    }

    protected function getResourcesClass(): string
    {
        return NoteConceptuelleResource::class;
    }

    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $champsData = $data['champs'] ?? [];

            // Créer ou récupérer l'idée de projet
            $noteConceptuelle = $this->getOrCreateNoteConceptuelle($data);

            $noteConceptuelle->noteConceptuelle = new DocumentResource($this->documentRepository->getFicheIdee());

            $noteConceptuelle->redigerPar = auth()->id();

            $noteConceptuelle->save();

            // Sauvegarder les champs dynamiques
            $this->saveDynamicFields($noteConceptuelle, $champsData);

            $noteConceptuelle->refresh();

            DB::commit();

            return (new $this->resourceClass($noteConceptuelle))
                ->additional(['message' => 'Note conceptuelle sauvegardée avec succès.'])
                ->response()
                ->setStatusCode(isset($data['id']) ? 200 : 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Obtenir ou créer une idée de projet
     */
    private function getOrCreateNoteConceptuelle(array $data): NoteConceptuelle
    {
        if (isset($data['id'])) {
            return $this->repository->findOrFail($data['id']);
        }

        $idee = $this->repository->getModel();

        // Initialiser ficheIdee avec la structure complète du formulaire dès la création
        if (!$idee->ficheIdee || empty($idee->ficheIdee)) {
            $idee->ficheIdee = [];
        }

        return $idee;
    }



    /**
     * Sauvegarder les champs dynamiques
     */
    private function saveDynamicFields(NoteConceptuelle $noteConceptuelle, array $champsData): void
    {

        $champsDefinitions = $this->documentRepository->getFicheIdee()->all_champs;

        // Indexer par attribut pour accès rapide
        $champsMap = $champsDefinitions->keyBy('attribut');

        $syncData = [];

        foreach ($champsData as $attribut => $valeur) {
            if (isset($champsMap[$attribut])) {
                $champ = $champsMap[$attribut];
                $syncData[$champ->id] = [
                    'valeur' => $valeur ?? null,
                    'commentaire' => null
                ];
            }
        }

        // Synchroniser tous les champs reçus
        if (!empty($syncData)) {
            $noteConceptuelle->champs()->sync($syncData);
        }
    }

    /**
     * Méthode de mise à jour améliorée
     */
    public function update($id, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $idee = $this->repository->findOrFail($id);

            $champsData = $data['champs'] ?? [];

            // Sauvegarder les champs dynamiques
            $this->saveDynamicFields($idee, $champsData);

            $idee->refresh();

            DB::commit();

            return (new $this->resourceClass($idee))
                ->additional(['message' => 'Note conceptuelle sauvegardée avec succès.'])
                ->response()
                ->setStatusCode(isset($data['id']) ? 200 : 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }
}