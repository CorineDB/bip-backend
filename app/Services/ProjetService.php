<?php

namespace App\Services;

use App\Enums\StatutIdee;
use App\Http\Resources\projets\ProjetsResource;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Services\Contracts\ProjetServiceInterface;
use Illuminate\Http\JsonResponse;
use Exception;

class ProjetService extends BaseService implements ProjetServiceInterface
{
    protected BaseRepositoryInterface $repository;

    public function __construct(
        ProjetRepositoryInterface $repository
    )
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return ProjetsResource::class;
    }

    /**
     * Récupérer les projets sélectionnables (en cours de maturation - statut différent de PRET)
     */
    public function getProjetsEnCoursMaturation(): JsonResponse
    {
        try {
            $projets = $this->repository->getModel()
                ->whereNot('statut', StatutIdee::PRET)
                ->latest()
                ->get();

            return ($this->resourceClass::collection($projets))
                ->additional([
                    'message' => 'Projets sélectionnables récupérés avec succès.',
                    'total' => $projets->count()
                ])
                ->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Récupérer les projets matures (arrivés à maturité - statut = PRET)
     */
    public function getProjetsArrivesAMaturite(): JsonResponse
    {
        try {
            $projets = $this->repository->getModel()
                ->where('statut', StatutIdee::PRET)
                ->latest()
                ->get();

            return ($this->resourceClass::collection($projets))
                ->additional([
                    'message' => 'Projets matures récupérés avec succès.',
                    'total' => $projets->count()
                ])
                ->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}