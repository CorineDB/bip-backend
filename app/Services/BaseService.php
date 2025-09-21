<?php

namespace App\Services;

use App\Repositories\Contracts\BaseRepositoryInterface;
use App\Services\Contracts\AbstractServiceInterface;
use App\Http\Resources\Contracts\ApiResourceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

abstract class BaseService implements AbstractServiceInterface
{
    protected BaseRepositoryInterface $repository;
    protected string $resourceClass;

    public function __construct(
        BaseRepositoryInterface $repository
    ) {
        $this->repository = $repository;
        $this->resourceClass = $this->getResourceClass();
    }

    /**
     * Get the resource class for this service
     */
    abstract protected function getResourceClass(): string;

    public function all(): JsonResponse
    {
        try {
            $data = $this->repository->all();
            return $this->resourceClass::collection($data)->response();
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function find(int|string $id): JsonResponse
    {
        try {
            $item = $this->repository->findOrFail($id);
            return (new $this->resourceClass($item))->response();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function create(array $data): JsonResponse
    {
        try {
            $item = $this->repository->create($data);
            return (new $this->resourceClass($item))
                ->additional(['message' => 'Resource created successfully.'])
                ->response()
                ->setStatusCode(201);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function update(int|string $id, array $data): JsonResponse
    {
        try {
            $updated = $this->repository->update($id, $data);
            if (!$updated) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found or not updated.',
                ], 404);
            }

            $item = $this->repository->findOrFail($id);

            return (new $this->resourceClass($item))
                ->additional(['message' => 'Resource updated successfully.'])
                ->response();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Resource not found.',
            ], 404);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    public function delete(int|string $id): JsonResponse
    {
        try {
            $deleted = $this->repository->delete($id);
            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Resource not found or not deleted.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Resource deleted successfully.',
            ]);
        } catch (Exception $e) {
            return $this->errorResponse($e);
        }
    }

    protected function errorResponse(Exception $e): JsonResponse
    {
        // Déterminer le code de statut selon le type d'exception
        $statusCode = 500;
        $message = 'Une erreur interne s\'est produite';
        $errors = [];

        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            $statusCode = 404;
            // Extraire le nom du modèle depuis l'exception
            $model = class_basename($e->getModel());
            $modelNames = [
                'Projet' => 'Projet',
                'User' => 'Utilisateur',
                'IdeeProjet' => 'Idée de projet',
                'Tdr' => 'Terme de reference',
                'Evaluation' => 'Évaluation',
                'Document' => 'Canevas ',
                'Fichier' => 'Fichier',
                'Champ' => 'Champ de formulaire',
                'Decision' => 'Décision',
                'Workflow' => 'Workflow',
                'Notification' => 'Notification',
                'Permission' => 'Permission',
                'Role' => 'Rôle',
                'GroupeUtilisateur' => 'Groupe d\'utilisateur',
                'Ministere' => 'Ministère',
                'Direction' => 'Direction',
                'Service' => 'Service',
                'CategorieCritere' => "Outil d'evaluation",
                'Critere' => 'Critère d\'évaluation',
                'NoteConceptuelle' => 'Note conceptuelle',
                'Rapport' => 'Rapport',
                'Commentaire' => 'Commentaire',
                'Notation' => 'Notation',
                'EvaluationCritere' => 'Évaluation de critère',
                'Personne' => 'Personne',
                'Organisation' => 'Organisation',
                'CategorieDocument' => 'Catégorie de document',
                'CategorieProjet' => 'Catégorie de projet',
                'Statut' => 'Statut',
                'TrackInfo' => 'Information de suivi',
                'Commune' => 'Commune',
                'Arrondissement' => 'Arrondissement',
                'Departement' => 'Département',
                'Village' => 'Village',
                'LieuIntervention' => 'Lieu d\'intervention',
                'Financement' => 'Financement',
                'TypeIntervention' => 'Type d\'intervention',
                'TypeProgramme' => 'Type de programme',
                'ComposantProgramme' => 'Composant de programme',
                'Secteur' => 'Secteur',
                'Cible' => 'Cible',
                'ChampSection' => 'Section de formulaire',
                'ChampProjet' => 'Champ de formulaire',
                'Dgpd' => 'DGPD',
                'Dpaf' => 'DPAF',
                'Odd' => 'Objectif de developpement durable'
            ];
            $resourceName = $modelNames[$model] ?? 'Ressource';
            $message = $resourceName . ' non trouvé(e)';
        } elseif ($e instanceof \Illuminate\Validation\ValidationException) {
            $statusCode = 422;
            $message = 'Erreurs de validation' ;
            $errors = $e->errors(); ;
        } elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {
            $statusCode = 401;
            $message = 'Non authentifié';
        } elseif ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
            $statusCode = 403;
            $message = 'Action non autorisée';
        } elseif ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpException) {
            $statusCode = $e->getStatusCode();
            $message = $e->getMessage() ?: 'Erreur HTTP';
        } else {
            // En production, ne pas exposer les détails des erreurs internes
            if (app()->environment('production')) {
                $message = 'Une erreur interne s\'est produite';
            } else {
                $message = $e->getMessage();
            }
        }

        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $statusCode);
    }
}