<?php

namespace App\Services;

use App\Enums\StatutIdee;
use App\Http\Resources\projets\integration\ProjetResource;
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

    /**
     * Récupérer un projet spécifique par son ID
     */
    public function getProjet(int $projetId): JsonResponse
    {
        try {
            $projet = $this->repository->find($projetId);

            if (!$projet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projet non trouvé.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Projet récupéré avec succès.',
                'data' => new ProjetResource($projet)
            ], 200);

        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * Mettre à jour le statut d'un projet
     */
    public function updateProjetStatus(int $projetId, array $data): JsonResponse
    {
        try {
            $nouveauStatut = $data['statut'];
            $est_ancien = $data['est_ancien'] ?? false;

            // Vérifier que le statut est valide
            $statutsValides = StatutIdee::values();
            if (!in_array($nouveauStatut, $statutsValides)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Statut invalide.',
                    'errors' => [
                        'statut' => ['Le statut fourni n\'est pas valide.']
                    ]
                ], 422);
            }

            // Récupérer le projet
            $projet = $this->repository->find($projetId);

            if (!$projet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Projet non trouvé.',
                ], 404);
            }

            // Vérifier que le projet est bien arrivé à maturité (PRET)
            if (!in_array($projet->statut, [StatutIdee::PRET, StatutIdee::SELECTION, StatutIdee::IDEE_DE_PROJET, StatutIdee::REJETE, StatutIdee::CLOTURE, StatutIdee::EN_COURS_EXECUTION, StatutIdee::EN_ATTENTE_DE_PROGRAMMATION, StatutIdee::EN_COURS_DE_MATURATION])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seuls les projets arrivés à maturité peuvent avoir leur statut modifié via cette API.',
                    'errors' => [
                        'statut' => ['Le projet doit avoir le statut PRET pour être mis à jour.']
                    ]
                ], 422);
            }

            // Mettre à jour le statut
            $projet->statut = $nouveauStatut;

            // Mettre à jour le flag "est_ancien" si fourni
            if (isset($data['est_ancien'])) {
                $projet->est_ancien = $est_ancien;
            }

            $projet->save();

            // Créer un commentaire avec tag d'identification SIGFP si fourni
            if (isset($data['commentaire']) && !empty($data['commentaire'])) {
                $tagIntegration = "[SIGFP-INTEGRATION] ";
                $commentaireAvecTag = $tagIntegration . $data['commentaire'];

                $projet->commentaires()->create([
                    'commentaire' => $commentaireAvecTag,
                    'commentateurId' => null, // Commentaire système (intégration SIGFP)
                    'date' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Statut du projet mis à jour avec succès.',
                'data' => new ProjetResource($projet)
            ], 200);

        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }
}
