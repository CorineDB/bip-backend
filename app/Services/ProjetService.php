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

    /**
     * Récupérer un projet avec ses détails complets
     * Surcharge la méthode find du BaseService pour inclure plus de relations
     */
    public function find(int|string $id): JsonResponse
    {
        try {
            $projet = $this->repository->getModel()
                ->with([
                    // Relations principales
                    'ideeProjet',
                    'secteur',
                    'ministere',
                    'categorie',
                    'responsable',
                    'demandeur',

                    // Relations liées aux documents et évaluations
                    'noteConceptuelle',
                    'evaluations',

                    // Relations TDRs avec détails complets
                    'tdrPrefaisabilite',
                    'tdrFaisabilite',

                    // Relations rapports
                    /* 'rapportsPrefaisabilite' => function($query) {
                        $query->with(['fichiers' => function($q) { $q->active()->ordered(); }])
                              ->orderBy('created_at', 'desc');
                    },
                    'rapportsFaisabilite' => function($query) {
                        $query->with(['fichiers' => function($q) { $q->active()->ordered(); }])
                              ->orderBy('created_at', 'desc');
                    },
                    'rapportsEvaluationExAnte' => function($query) {
                        $query->with(['fichiers' => function($q) { $q->active()->ordered(); }])
                              ->orderBy('created_at', 'desc');
                    }, */

                    // Relations de financement et partenaires
                    'sources_de_financement',
                    'financements',

                    // Relations géographiques et typologiques
                    'lieuxIntervention',
                    'cibles',
                    'odds',
                    'typesIntervention',

                    // Relations programmes
                    'orientations_strategique_png',
                    'objectifs_strategique_png',
                    'resultats_strategique_png',
                    'axes_pag',
                    'actions_pag',
                    'pilliers_pag',

                    // Fichiers attachés
                    'fichiers' => function($query) {
                        $query->active()->ordered();
                    },

                    // Workflows et commentaires
                    'workflows',
                    'commentaires',

                    // Décisions
                    'decisions'
                ])
                ->findOrFail($id);

            return (new $this->resourceClass($projet))
                ->response();

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Projet non trouvé.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}