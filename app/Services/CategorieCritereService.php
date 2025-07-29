<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\CategorieCritereRepositoryInterface;
use App\Services\Contracts\CategorieCritereServiceInterface;
use App\Http\Resources\CategorieCritereResource;
use App\Models\Critere;
use App\Models\Notation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategorieCritereService extends BaseService implements CategorieCritereServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        CategorieCritereRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return CategorieCritereResource::class;
    }

    public function create(array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $criteres = $data['criteres'] ?? [];
            $notationsCategorie = $data['notations'] ?? [];
            unset($data['criteres'], $data['notations']);

            $categorieCritere = $this->repository->create($data);

            if (!empty($notationsCategorie)) {
                foreach ($notationsCategorie as $notationData) {
                    $notationData['categorie_critere_id'] = $categorieCritere->id;
                    $notationData['critere_id'] = null;

                    Notation::create($notationData);
                }
            }

            if (!empty($criteres)) {
                foreach ($criteres as $critereData) {
                    $critereData['categorie_critere_id'] = $categorieCritere->id;

                    $notations = $critereData['notations'] ?? [];
                    unset($critereData['notations']);

                    $critere = Critere::create($critereData);

                    if (!empty($notations)) {
                        foreach ($notations as $notationData) {
                            $notationData['critere_id'] = $critere->id;
                            $notationData['categorie_critere_id'] = $categorieCritere->id;

                            Notation::create($notationData);
                        }
                    }
                }
            }

            DB::commit();

            return (new $this->resourceClass($categorieCritere->load(['criteres.notations', 'notations'])))
                ->additional(['message' => 'Catégorie critère créée avec succès.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    public function update(int|string $id, array $data): JsonResponse
    {
        try {
            DB::beginTransaction();

            $categorieCritere = $this->repository->findOrFail($id);

            $criteres = $data['criteres'] ?? [];
            $notationsCategorie = $data['notations'] ?? [];
            unset($data['criteres'], $data['notations']);

            if (!empty($data)) {
                $this->repository->update($id, $data);
            }

            if (!empty($notationsCategorie)) {
                foreach ($notationsCategorie as $notationData) {
                    if (isset($notationData['id']) && $notationData['id']) {
                        $notation = Notation::findOrFail($notationData['id']);
                        $notation->update($notationData);
                    } else {
                        $notationData['categorie_critere_id'] = $id;
                        $notationData['critere_id'] = null;
                        Notation::create($notationData);
                    }
                }
            }

            if (!empty($criteres)) {
                foreach ($criteres as $critereData) {
                    $notations = $critereData['notations'] ?? [];
                    unset($critereData['notations']);

                    if (isset($critereData['id']) && $critereData['id']) {
                        $critere = Critere::findOrFail($critereData['id']);
                        $critere->update($critereData);
                    } else {
                        $critereData['categorie_critere_id'] = $id;
                        $critere = Critere::create($critereData);
                    }

                    if (!empty($notations)) {
                        foreach ($notations as $notationData) {
                            if (isset($notationData['id']) && $notationData['id']) {
                                $notation = Notation::findOrFail($notationData['id']);
                                $notation->update($notationData);
                            } else {
                                $notationData['critere_id'] = $critere->id;
                                $notationData['categorie_critere_id'] = $id;
                                Notation::create($notationData);
                            }
                        }
                    }
                }
            }

            DB::commit();

            $updatedCategorie = $this->repository->findOrFail($id);

            return (new $this->resourceClass($updatedCategorie->load(['criteres.notations', 'notations'])))
                ->additional(['message' => 'Catégorie critère mise à jour avec succès.'])
                ->response();
        } catch (Exception $e) {
            DB::rollBack();
            return $this->errorResponse($e);
        }
    }

    /**
     * Get the grille evaluation preliminaire des impacts climatique
     */
    public function getGrilleEvaluationPreliminaire(): JsonResponse
    {
        try {
            $grille = $this->repository->findByType('Évaluation préliminaire multi projet de l\'impact climatique');

            if (!$grille) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grille d\'évaluation préliminaire non trouvée.',
                ], 404);
            }

            return (new $this->resourceClass($grille->load(['criteres.notations', 'notations'])))
                ->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Update the grille evaluation preliminaire des impacts climatique
     */
    public function updateGrilleEvaluationPreliminaire(array $data): JsonResponse
    {
        try {
            $grille = $this->repository->findByType('Évaluation préliminaire multi projet de l\'impact climatique');

            if (!$grille) {
                return response()->json([
                    'success' => false,
                    'message' => 'Grille d\'évaluation préliminaire non trouvée.',
                ], 404);
            }

            return $this->update($grille->id, $data);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}