<?php

namespace App\Repositories\Contracts;

use App\Models\Projet;
use App\Models\Tdr;
use Illuminate\Database\Eloquent\Collection;

interface ProjetRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Obtenir un projet avec ses TDRs actifs et historique
     */
    public function getProjetAvecTdrs(int $projetId): ?Projet;

    /**
     * Obtenir un projet avec le TDR de préfaisabilité actif
     */
    public function getProjetAvecTdrPrefaisabilite(int $projetId): ?Projet;

    /**
     * Obtenir un projet avec le TDR de faisabilité actif  
     */
    public function getProjetAvecTdrFaisabilite(int $projetId): ?Projet;

    /**
     * Obtenir les projets ayant des TDRs en attente d'évaluation
     */
    public function getProjetsAvecTdrsEnAttente(): Collection;

    /**
     * Obtenir les projets avec historique complet des TDRs
     */
    public function getProjetsAvecHistoriqueTdrs(array $projetIds = []): Collection;

    /**
     * Obtenir les statistiques des TDRs par projet
     */
    public function getStatistiquesTdrsProjets(): array;
}