<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\categories_critere\checklistMesuresAdaptation\CreateOrUpdateChecklistRequest;
use App\Http\Requests\categories_critere\StoreCategorieCritereRequest;
use App\Http\Requests\categories_critere\UpdateCategorieCritereRequest;
use App\Services\Contracts\CategorieCritereServiceInterface;
use Illuminate\Http\JsonResponse;

class CategorieCritereController extends Controller
{
    protected CategorieCritereServiceInterface $service;

    public function __construct(CategorieCritereServiceInterface $service)
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

    public function store(StoreCategorieCritereRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateCategorieCritereRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Récupérer la grille d'évaluation préliminaire des impacts climatiques
     *
     * @OA\Get(
     *     path="/api/grille-evaluation-preliminaire",
     *     tags={"Évaluations - Configuration"},
     *     summary="Récupérer la grille d'évaluation préliminaire",
     *     description="Récupère la configuration de la grille d'évaluation préliminaire des impacts climatiques avec tous les critères et sous-critères",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Grille d'évaluation préliminaire récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Grille d'évaluation préliminaire récupérée avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom", type="string", example="Adaptation au changement climatique"),
     *                         @OA\Property(property="description", type="string", example="Critères d'évaluation de l'adaptation climatique"),
     *                         @OA\Property(property="poids", type="number", format="float", example=0.4),
     *                         @OA\Property(property="criteres", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="nom", type="string", example="Résilience aux événements climatiques"),
     *                                 @OA\Property(property="description", type="string"),
     *                                 @OA\Property(property="poids", type="number", format="float", example=0.3),
     *                                 @OA\Property(property="unite", type="string", example="score"),
     *                                 @OA\Property(property="echelle_notation", type="object",
     *                                     @OA\Property(property="min", type="integer", example=1),
     *                                     @OA\Property(property="max", type="integer", example=5)
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération de la grille")
     *         )
     *     )
     * )
     */
    public function getGrilleEvaluationPreliminaire(): JsonResponse
    {
        return $this->service->getGrilleEvaluationPreliminaire();
    }

    /**
     * Mettre à jour la grille d'évaluation préliminaire des impacts climatiques
     *
     * @OA\Put(
     *     path="/api/grille-evaluation-preliminaire",
     *     tags={"Évaluations - Configuration"},
     *     summary="Mettre à jour la grille d'évaluation préliminaire",
     *     description="Met à jour la configuration de la grille d'évaluation préliminaire des impacts climatiques (critères, poids, échelles de notation)",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom", type="string", example="Adaptation au changement climatique"),
     *                         @OA\Property(property="description", type="string", example="Critères d'évaluation de l'adaptation climatique"),
     *                         @OA\Property(property="poids", type="number", format="float", example=0.4),
     *                         @OA\Property(property="criteres", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="nom", type="string", example="Résilience aux événements climatiques"),
     *                                 @OA\Property(property="description", type="string", example="Capacité du projet à résister aux chocs climatiques"),
     *                                 @OA\Property(property="poids", type="number", format="float", example=0.3),
     *                                 @OA\Property(property="unite", type="string", example="score"),
     *                                 @OA\Property(property="echelle_notation", type="object",
     *                                     @OA\Property(property="min", type="integer", example=1),
     *                                     @OA\Property(property="max", type="integer", example=5)
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grille d'évaluation préliminaire mise à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Grille d'évaluation préliminaire mise à jour avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function updateGrilleEvaluationPreliminaire(UpdateCategorieCritereRequest $request): JsonResponse
    {
        return $this->service->updateGrilleEvaluationPreliminaire($request->all());
    }

    /**
     * Récupérer la grille d'analyse multicritères
     *
     * @OA\Get(
     *     path="/api/grille-analyse-multi-critere",
     *     tags={"Évaluations - Configuration"},
     *     summary="Récupérer la grille d'analyse multicritères",
     *     description="Récupère la configuration de la grille d'analyse multicritères avec tous les critères, sous-critères, poids et échelles de notation pour l'évaluation des projets",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Grille d'analyse multicritères récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Grille d'analyse multicritères récupérée avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom", type="string", example="Impact environnemental"),
     *                         @OA\Property(property="description", type="string", example="Évaluation de l'impact du projet sur l'environnement"),
     *                         @OA\Property(property="poids", type="number", format="float", example=0.3),
     *                         @OA\Property(property="criteres", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="nom", type="string", example="Réduction des émissions de GES"),
     *                                 @OA\Property(property="description", type="string", example="Potentiel de réduction des gaz à effet de serre"),
     *                                 @OA\Property(property="poids", type="number", format="float", example=0.4),
     *                                 @OA\Property(property="unite", type="string", example="tCO2e/an"),
     *                                 @OA\Property(property="echelle_notation", type="object",
     *                                     @OA\Property(property="min", type="integer", example=0),
     *                                     @OA\Property(property="max", type="integer", example=10),
     *                                     @OA\Property(property="labels", type="object",
     *                                         @OA\Property(property="0", type="string", example="Aucun impact"),
     *                                         @OA\Property(property="5", type="string", example="Impact modéré"),
     *                                         @OA\Property(property="10", type="string", example="Impact élevé")
     *                                     )
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="methode_aggregation", type="string", example="somme_ponderee"),
     *                 @OA\Property(property="seuils_decision", type="object",
     *                     @OA\Property(property="acceptable", type="number", format="float", example=6.0),
     *                     @OA\Property(property="bon", type="number", format="float", example=7.5),
     *                     @OA\Property(property="excellent", type="number", format="float", example=9.0)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération de la grille")
     *         )
     *     )
     * )
     */
    public function getGrilleAnalyseMultiCriteres(): JsonResponse
    {
        return $this->service->getGrilleAnalyseMultiCriteres();
    }

    /**
     * Get the grille analyse multi-criteres with evaluations for an idee de projet
     */
    public function getGrilleAnalyseMultiCriteresAvecEvaluations($ideeProjetId): JsonResponse
    {
        return $this->service->getGrilleAnalyseMultiCriteresAvecEvaluations($ideeProjetId);
    }

    /**
     * Mettre à jour la grille d'analyse multicritères
     *
     * @OA\Put(
     *     path="/api/grille-analyse-multi-critere",
     *     tags={"Évaluations - Configuration"},
     *     summary="Mettre à jour la grille d'analyse multicritères",
     *     description="Met à jour la configuration de la grille d'analyse multicritères (critères, poids, échelles de notation, seuils de décision)",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="categories", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nom", type="string", example="Impact environnemental"),
     *                         @OA\Property(property="description", type="string", example="Évaluation de l'impact du projet sur l'environnement"),
     *                         @OA\Property(property="poids", type="number", format="float", example=0.3),
     *                         @OA\Property(property="criteres", type="array",
     *                             @OA\Items(type="object",
     *                                 @OA\Property(property="id", type="integer", example=1),
     *                                 @OA\Property(property="nom", type="string", example="Réduction des émissions de GES"),
     *                                 @OA\Property(property="description", type="string", example="Potentiel de réduction des gaz à effet de serre"),
     *                                 @OA\Property(property="poids", type="number", format="float", example=0.4),
     *                                 @OA\Property(property="unite", type="string", example="tCO2e/an"),
     *                                 @OA\Property(property="echelle_notation", type="object",
     *                                     @OA\Property(property="min", type="integer", example=0),
     *                                     @OA\Property(property="max", type="integer", example=10),
     *                                     @OA\Property(property="labels", type="object",
     *                                         @OA\Property(property="0", type="string", example="Aucun impact"),
     *                                         @OA\Property(property="5", type="string", example="Impact modéré"),
     *                                         @OA\Property(property="10", type="string", example="Impact élevé")
     *                                     )
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="methode_aggregation", type="string", example="somme_ponderee", description="Méthode d'agrégation des scores"),
     *                 @OA\Property(property="seuils_decision", type="object",
     *                     @OA\Property(property="acceptable", type="number", format="float", example=6.0),
     *                     @OA\Property(property="bon", type="number", format="float", example=7.5),
     *                     @OA\Property(property="excellent", type="number", format="float", example=9.0)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grille d'analyse multicritères mise à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Grille d'analyse multicritères mise à jour avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur de validation"),
     *             @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="poids", type="array",
     *                     @OA\Items(type="string", example="La somme des poids doit être égale à 1.0")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Non authentifié",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Token invalide")
     *         )
     *     )
     * )
     */
    public function updateGrilleAnalyseMultiCriteres(UpdateCategorieCritereRequest $request): JsonResponse
    {
        return $this->service->updateGrilleAnalyseMultiCriteres($request->all());
    }

    /**
     * Récupérer la checklist des mesures d'adaptation pour projets à haut risque
     */
    public function getChecklistMesuresAdaptation(): JsonResponse
    {
        return $this->service->getChecklistMesuresAdaptation();
    }

    /**
     * Créer ou mettre à jour la checklist des mesures d'adaptation
     */
    public function createOrUpdateChecklistMesuresAdaptation(CreateOrUpdateChecklistRequest $request): JsonResponse
    {
        return $this->service->createOrUpdateChecklistMesuresAdaptation($request->validated());
    }

}