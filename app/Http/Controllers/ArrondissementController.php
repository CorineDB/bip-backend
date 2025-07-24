<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Contracts\ArrondissementServiceInterface;
use Illuminate\Http\JsonResponse;

class ArrondissementController extends Controller
{
    protected ArrondissementServiceInterface $service;

    public function __construct(ArrondissementServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *     path="/api/arrondissements",
     *     operationId="getArrondissementsList",
     *     tags={"Arrondissements"},
     *     summary="Get la liste des arrondissements",
     *     description="Returns all arrondissements",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Arrondissement")
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    public function villages($id): JsonResponse
    {
        return $this->service->villages($id);
    }
}
/**
 * @OA\Schema(
 *     schema="Arrondissement",
 *     type="object",
 *     title="Arrondissement",
 *     required={"id", "nom"},
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nom", type="string", example="Abomey-Calavi")
 * )
 */
