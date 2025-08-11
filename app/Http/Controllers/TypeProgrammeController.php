<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\types_programme\StoreTypeProgrammeRequest;
use App\Http\Requests\types_programme\UpdateTypeProgrammeRequest;
use App\Services\Contracts\TypeProgrammeServiceInterface;
use Illuminate\Http\JsonResponse;

class TypeProgrammeController extends Controller
{
    protected TypeProgrammeServiceInterface $service;

    public function __construct(TypeProgrammeServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * Liste tous les types de programmes
     *
     * @OA\Get(
     *     path="/api/types-programme",
     *     tags={"Types de Programmes"},
     *     summary="Récupérer tous les types de programmes",
     *     description="Récupère la liste complète des types de programmes disponibles",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des types de programmes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Liste des types de programmes"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Programme National"),
     *                     @OA\Property(property="description", type="string", example="Description du programme national"),
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
     * Affiche un type de programme spécifique
     *
     * @OA\Get(
     *     path="/api/types-programme/{id}",
     *     tags={"Types de Programmes"},
     *     summary="Récupérer un type de programme par ID",
     *     description="Récupère les détails d'un type de programme spécifique",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de programme",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de programme trouvé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Type de programme trouvé"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Programme National"),
     *                 @OA\Property(property="description", type="string", example="Description du programme national"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de programme non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Type de programme non trouvé")
     *         )
     *     )
     * )
     */
    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    /**
     * Crée un nouveau type de programme
     *
     * @OA\Post(
     *     path="/api/types-programme",
     *     tags={"Types de Programmes"},
     *     summary="Créer un nouveau type de programme",
     *     description="Crée un nouveau type de programme avec les données fournies",
     *     security={{"passport": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nom"},
     *             @OA\Property(property="nom", type="string", example="Nouveau Programme"),
     *             @OA\Property(property="description", type="string", example="Description du nouveau programme")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Type de programme créé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Type de programme créé avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=2),
     *                 @OA\Property(property="nom", type="string", example="Nouveau Programme"),
     *                 @OA\Property(property="description", type="string", example="Description du nouveau programme"),
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
    public function store(StoreTypeProgrammeRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    /**
     * Met à jour un type de programme
     *
     * @OA\Put(
     *     path="/api/types-programme/{id}",
     *     tags={"Types de Programmes"},
     *     summary="Mettre à jour un type de programme",
     *     description="Met à jour les informations d'un type de programme existant",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de programme",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="nom", type="string", example="Programme Modifié"),
     *             @OA\Property(property="description", type="string", example="Description modifiée")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de programme mis à jour avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Type de programme mis à jour avec succès"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nom", type="string", example="Programme Modifié"),
     *                 @OA\Property(property="description", type="string", example="Description modifiée"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de programme non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Type de programme non trouvé")
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
    public function update(UpdateTypeProgrammeRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    /**
     * Supprime un type de programme
     *
     * @OA\Delete(
     *     path="/api/types-programme/{id}",
     *     tags={"Types de Programmes"},
     *     summary="Supprimer un type de programme",
     *     description="Supprime définitivement un type de programme",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du type de programme",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Type de programme supprimé avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Type de programme supprimé avec succès")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Type de programme non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Type de programme non trouvé")
     *         )
     *     )
     * )
     */
    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Récupérer la liste des programmes
     *
     * @OA\Get(
     *     path="/api/programmes",
     *     tags={"Programmes"},
     *     summary="Récupérer la liste des programmes",
     *     description="Récupère la liste complète des programmes disponibles dans le système",
     *     security={{"passport": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Liste des programmes récupérée avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Liste des programmes"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Programme de Développement Rural"),
     *                     @OA\Property(property="description", type="string", example="Programme axé sur le développement des zones rurales"),
     *                     @OA\Property(property="type_programme_id", type="integer", example=1),
     *                     @OA\Property(property="statut", type="string", example="actif"),
     *                     @OA\Property(property="budget_total", type="number", format="float", example=10000000),
     *                     @OA\Property(property="date_debut", type="string", format="date"),
     *                     @OA\Property(property="date_fin", type="string", format="date"),
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
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erreur serveur",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Erreur lors de la récupération des programmes")
     *         )
     *     )
     * )
     */
    public function programmes(): JsonResponse
    {
        return $this->service->programmes();
    }

    /**
     * Récupérer les composants d'un programme
     *
     * @OA\Get(
     *     path="/api/programmes/{id}/composants-programme",
     *     tags={"Programmes"},
     *     summary="Récupérer les composants d'un programme",
     *     description="Récupère tous les composants associés à un programme spécifique",
     *     security={{"passport": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID du programme",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Composants du programme récupérés avec succès",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="message", type="string", example="Composants du programme"),
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="nom", type="string", example="Composant Infrastructure"),
     *                     @OA\Property(property="description", type="string", example="Développement des infrastructures de base"),
     *                     @OA\Property(property="programme_id", type="integer", example=1),
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
     *         description="Programme non trouvé",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="error"),
     *             @OA\Property(property="message", type="string", example="Programme non trouvé")
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
    public function composants_de_programme($idProgramme): JsonResponse
    {
        return $this->service->composants_de_programme($idProgramme);
    }
}
