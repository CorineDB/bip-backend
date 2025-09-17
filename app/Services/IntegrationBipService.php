<?php

namespace App\Services;

use App\Enums\StatutIdee;
use Illuminate\Http\JsonResponse;
use Exception;
use App\Services\BaseService;
use App\Http\Resources\projets\ProjetsResource;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Services\Contracts\IntegrationBipServiceInterface;

class IntegrationBipService extends BaseService implements IntegrationBipServiceInterface
{
    public function __construct(ProjetRepositoryInterface $repository)
    {
        parent::__construct($repository);
    }

    protected function getResourceClass(): string
    {
        return ProjetsResource::class;
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
