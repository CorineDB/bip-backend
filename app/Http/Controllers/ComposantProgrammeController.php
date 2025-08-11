<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\composants_programme\StoreComposantProgrammeRequest;
use App\Http\Requests\composants_programme\UpdateComposantProgrammeRequest;
use App\Services\Contracts\ComposantProgrammeServiceInterface;
use Illuminate\Http\JsonResponse;

class ComposantProgrammeController extends Controller
{
    protected ComposantProgrammeServiceInterface $service;

    public function __construct(ComposantProgrammeServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Liste tous les composants de programmes
     *
     * @OA\Get(
     *     path="/api/composants-programme",
     *     tags={"Composants de Programmes"},
     *     summary="Récupérer tous les composants de programmes",
     *     description="Récupère la liste complète des composants de programmes disponibles",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des composants de programmes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Liste des composants de programmes"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Infrastructure Routière"),
     *                     @OA\Property(property="description", type="string", example="Composant dédié au développement des infrastructures routières"),
     *                     @OA\Property(property="type_composant", type="string", example="infrastructure"),
     *                     @OA\Property(property="budget_alloue", type="number", format="float", example=5000000),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
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
    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    /**
     * Affiche un composant de programme spécifique
     *
     * @OA\Get(
     *     path="/api/composants-programme/{id}",
     *     tags={"Composants de Programmes"},
     *     summary="Récupérer un composant de programme par ID",
     *     description="Récupère les détails d'un composant de programme spécifique",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du composant de programme",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Composant de programme trouvé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Composant de programme trouvé"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Infrastructure Routière"),
     *                 @OA\Property(property="description", type="string", example="Composant dédié au développement des infrastructures routières"),
     *                 @OA\Property(property="type_composant", type="string", example="infrastructure"),
     *                 @OA\Property(property="budget_alloue", type="number", format="float", example=5000000),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Composant de programme non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Composant de programme non trouvé")
     *         )
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    /**
     * Crée un nouveau composant de programme
     *
     * @OA\Post(
     *     path="/api/composants-programme",
     *     tags={"Composants de Programmes"},
     *     summary="Créer un nouveau composant de programme",
     *     description="Crée un nouveau composant de programme avec les données fournies",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom"},
     *             @OA\Property(property="nom", type="string", example="Nouveau Composant"),
     *             @OA\Property(property="description", type="string", example="Description du nouveau composant"),
     *             @OA\Property(property="type_composant", type="string", example="education"),
     *             @OA\Property(property="budget_alloue", type="number", format="float", example=2000000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Composant de programme créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Composant de programme créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="nom", type="string", example="Nouveau Composant"),
     *                 @OA\Property(property="description", type="string", example="Description du nouveau composant"),
     *                 @OA\Property(property="type_composant", type="string", example="education"),
     *                 @OA\Property(property="budget_alloue", type="number", format="float", example=2000000),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erreur de validation",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Données invalides"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(StoreComposantProgrammeRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    /**
     * Met à jour un composant de programme
     *
     * @OA\Put(
     *     path="/api/composants-programme/{id}",
     *     tags={"Composants de Programmes"},
     *     summary="Mettre à jour un composant de programme",
     *     description="Met à jour les informations d'un composant de programme existant",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du composant de programme",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nom", type="string", example="Composant Modifié"),
     *             @OA\Property(property="description", type="string", example="Description modifiée"),
     *             @OA\Property(property="budget_alloue", type="number", format="float", example=2500000)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Composant de programme mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Composant de programme mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Composant Modifié"),
     *                 @OA\Property(property="description", type="string", example="Description modifiée"),
     *                 @OA\Property(property="budget_alloue", type="number", format="float", example=2500000),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Composant de programme non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Composant de programme non trouvé")
     *         )
     *     )
     * )
     */
    public function update(UpdateComposantProgrammeRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    /**
     * Supprime un composant de programme
     *
     * @OA\Delete(
     *     path="/api/composants-programme/{id}",
     *     tags={"Composants de Programmes"},
     *     summary="Supprimer un composant de programme",
     *     description="Supprime définitivement un composant de programme",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du composant de programme",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Composant de programme supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Composant de programme supprimé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Composant de programme non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Composant de programme non trouvé")
     *         )
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Récupère les axes du Programme d'Actions du Gouvernement (PAG)
     *
     * @OA\Get(
     *     path="/api/axes-pag",
     *     tags={"PAG - Programme d'Actions du Gouvernement"},
     *     summary="Récupérer les axes PAG",
     *     description="Récupère la liste des axes du Programme d'Actions du Gouvernement",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Axes PAG récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Axes PAG récupérés avec succès"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Axe 1: Renforcement de la démocratie"),
     *                     @OA\Property(property="description", type="string", example="Renforcement de l'État de droit et de la gouvernance"),
     *                     @OA\Property(property="ordre", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function axesPag(): JsonResponse
    {
        return $this->service->axes_pag();
    }

    /**
     * Récupère les piliers du Programme d'Actions du Gouvernement (PAG)
     *
     * @OA\Get(
     *     path="/api/piliers-pag",
     *     tags={"PAG - Programme d'Actions du Gouvernement"},
     *     summary="Récupérer les piliers PAG",
     *     description="Récupère la liste des piliers du Programme d'Actions du Gouvernement",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Piliers PAG récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Piliers PAG récupérés avec succès"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Pilier Gouvernance"),
     *                     @OA\Property(property="description", type="string", example="Pilier dédié à l'amélioration de la gouvernance"),
     *                     @OA\Property(property="axe_id", type="integer", example=1),
     *                     @OA\Property(property="ordre", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function piliersPag(): JsonResponse
    {
        return $this->service->piliers_pag();
    }

    /**
     * Récupère les actions du Programme d'Actions du Gouvernement (PAG)
     *
     * @OA\Get(
     *     path="/api/actions-pag",
     *     tags={"PAG - Programme d'Actions du Gouvernement"},
     *     summary="Récupérer les actions PAG",
     *     description="Récupère la liste des actions du Programme d'Actions du Gouvernement",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Actions PAG récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Actions PAG récupérées avec succès"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Réforme de la justice"),
     *                     @OA\Property(property="description", type="string", example="Modernisation du système judiciaire"),
     *                     @OA\Property(property="pilier_id", type="integer", example=1),
     *                     @OA\Property(property="budget", type="number", format="float", example=1000000),
     *                     @OA\Property(property="delai", type="string", example="24 mois")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function actionsPag(): JsonResponse
    {
        return $this->service->actions_pag();
    }

    /**
     * Récupère les orientations stratégiques du Plan National de Développement (PND)
     *
     * @OA\Get(
     *     path="/api/orientations-strategiques-pnd",
     *     tags={"PND - Plan National de Développement"},
     *     summary="Récupérer les orientations stratégiques PND",
     *     description="Récupère la liste des orientations stratégiques du Plan National de Développement",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Orientations stratégiques PND récupérées avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Orientations stratégiques PND récupérées avec succès"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Développement du capital humain"),
     *                     @OA\Property(property="description", type="string", example="Amélioration des compétences et du bien-être de la population"),
     *                     @OA\Property(property="priorite", type="integer", example=1)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function orientationsStrategiquesPnd(): JsonResponse
    {
        return $this->service->orientations_strategiques_pnd();
    }

    /**
     * Récupère les objectifs stratégiques du Plan National de Développement (PND)
     *
     * @OA\Get(
     *     path="/api/objectifs-strategiques-pnd",
     *     tags={"PND - Plan National de Développement"},
     *     summary="Récupérer les objectifs stratégiques PND",
     *     description="Récupère la liste des objectifs stratégiques du Plan National de Développement",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Objectifs stratégiques PND récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Objectifs stratégiques PND récupérés avec succès"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Améliorer l'accès à l'éducation de qualité"),
     *                     @OA\Property(property="description", type="string", example="Objectif visant l'amélioration du système éducatif"),
     *                     @OA\Property(property="orientation_strategique_id", type="integer", example=1),
     *                     @OA\Property(property="indicateurs", type="array", @OA\Items(type="string"), example={"Taux de scolarisation", "Taux de réussite"})
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function objectifsStrategiquesPnd(): JsonResponse
    {
        return $this->service->objectifs_strategiques_pnd();
    }

    /**
     * Récupère les résultats stratégiques du Plan National de Développement (PND)
     *
     * @OA\Get(
     *     path="/api/resultats-strategiques-pnd",
     *     tags={"PND - Plan National de Développement"},
     *     summary="Récupérer les résultats stratégiques PND",
     *     description="Récupère la liste des résultats stratégiques du Plan National de Développement",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Résultats stratégiques PND récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Résultats stratégiques PND récupérés avec succès"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Augmentation du taux d'alphabétisation"),
     *                     @OA\Property(property="description", type="string", example="Résultat attendu de l'amélioration de l'éducation"),
     *                     @OA\Property(property="objectif_strategique_id", type="integer", example=1),
     *                     @OA\Property(property="valeur_cible", type="string", example="95%"),
     *                     @OA\Property(property="delai", type="string", example="2030")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function resultatsStrategiquesPnd(): JsonResponse
    {
        return $this->service->resultats_strategiques_pnd();
    }

    /**
     * Récupère les composants d'un type de programme
     *
     * @OA\Get(
     *     path="/api/composants-programme/{id}",
     *     tags={"Composants de Programmes"},
     *     summary="Récupérer les composants d'un type de programme",
     *     description="Récupère tous les composants associés à un type de programme spécifique",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de composant de programme",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Composants du type de programme récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Composants du type de programme"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Composant Infrastructure"),
     *                     @OA\Property(property="description", type="string", example="Développement des infrastructures de base"),
     *                     @OA\Property(property="type_programme_id", type="integer", example=1),
     *                     @OA\Property(property="budget_alloue", type="number", format="float", example=5000000),
     *                     @OA\Property(property="responsable", type="string", example="Jean Dupont"),
     *                     @OA\Property(property="statut", type="string", example="en_cours"),
     *                     @OA\Property(property="created_at", type="string", format="date-time"),
     *                     @OA\Property(property="updated_at", type="string", format="date-time")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de composant de programme non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Type de composant de programme non trouvé")
     *         )
     *     )
     * )
     */
    public function composants_de_programme($idComposantTypeProgramme): JsonResponse
    {
        return $this->service->composants_de_programme($idComposantTypeProgramme);
    }
}
