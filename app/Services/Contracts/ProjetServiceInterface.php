<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface ProjetServiceInterface extends AbstractServiceInterface
{
    /**
     * Récupérer les projets sélectionnables (en cours de maturation - statut différent de PRET)
     */
    public function getProjetsEnCoursMaturation(): JsonResponse;

    /**
     * Récupérer les projets matures (arrivés à maturité - statut = PRET)
     */
    public function getProjetsArrivesAMaturite(): JsonResponse;



    /**
     * Récupérer un projet mature (arrivés à maturité - statut = [PRET, SELECTION])
     */
    public function getProjetAMaturite(int $projetId): JsonResponse;
}
