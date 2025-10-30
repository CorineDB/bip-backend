<?php

namespace App\Services;

use App\Enums\StatutIdee;
use App\Http\Resources\projets\ProjetResource;
use App\Http\Resources\projets\ProjetsResource;
use App\Models\Dgpd;
use App\Models\Dpaf;
use App\Models\Organisation;
use App\Services\BaseService;
use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Repositories\Contracts\ProjetRepositoryInterface;
use App\Services\Contracts\ProjetServiceInterface;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\Auth;

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

    public function all(): JsonResponse
    {
        try {

            // NOUVEAU CODE (simplifié et clarifié)
            $item = $this->repository->getModel()->when(auth()->user()->profilable_type == Dpaf::class, function ($query) {
                $query->where("ministereId", Auth::user()->profilable->ministere->id);
            })->when(auth()->user()->profilable_type == Organisation::class, function ($query) {
                $ministereId = Auth::user()->profilable->ministere->id;

                // Filtrer par ministère pour tous les utilisateurs d'organisation
                $query->where("ministereId", $ministereId);

                // Responsable de projet : uniquement ses propres idées de son ministère
                if (auth()->user()->type == "responsable-projet") {
                    $query->where("responsableId", Auth::user()->id);
                }
                // Responsable hiérarchique : toutes les idées du ministère sauf brouillon
                elseif (auth()->user()->type == "responsable-hierachique") {
                    // Action
                }
                // Organisation : toutes les idées du ministère
                elseif (auth()->user()->type == "organisation") {
                }
            })->when(auth()->user()->profilable_type == Dgpd::class, function ($query) {
                //$query->whereIn("statut", [StatutIdee::ANALYSE, StatutIdee::AMC, StatutIdee::VALIDATION]);
            })->latest()->get();

            return ($this->resourceClass::collection($item))->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
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



            // NOUVEAU CODE (simplifié et clarifié)
            $projets = $this->repository->getModel()->whereNot('statut', StatutIdee::PRET)->when(auth()->user()->profilable_type == Dpaf::class, function ($query) {
                $query->where("ministereId", Auth::user()->profilable->ministere->id);
            })->when(auth()->user()->profilable_type == Organisation::class, function ($query) {
                $ministereId = Auth::user()->profilable->ministere->id;

                // Filtrer par ministère pour tous les utilisateurs d'organisation
                $query->where("ministereId", $ministereId);

                // Responsable de projet : uniquement ses propres idées de son ministère
                if (auth()->user()->type == "responsable-projet") {
                    $query->where("responsableId", Auth::user()->id);
                }
                // Responsable hiérarchique : toutes les idées du ministère sauf brouillon
                elseif (auth()->user()->type == "responsable-hierachique") {
                    // Action
                }
                // Organisation : toutes les idées du ministère
                elseif (auth()->user()->type == "organisation") {
                }
            })->when(auth()->user()->profilable_type == Dgpd::class, function ($query) {
                //$query->whereIn("statut", [StatutIdee::ANALYSE, StatutIdee::AMC, StatutIdee::VALIDATION]);
            })->latest()->get();

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
                    'rapportPrefaisabilite',
                    'rapportFaisabilite',
                    'rapportEvaluationExAnte',

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
                    'piliers_pag',

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

            return (new ProjetResource($projet))
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
