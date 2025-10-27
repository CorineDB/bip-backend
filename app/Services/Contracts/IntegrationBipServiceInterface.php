<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface IntegrationBipServiceInterface extends AbstractServiceInterface
{

    /**
     * Récupérer les projets matures (arrivés à maturité - statut = PRET)
     */
    public function getProjetsArrivesAMaturite(): JsonResponse;

    /**
     * Récupérer un projet spécifique par son ID
     */
    public function getProjet(int $projetId): JsonResponse;

    /**
     * Mettre à jour le statut d'un projet
     */
    public function updateProjetStatus(int $projetId, array $data): JsonResponse;
}