<?php

namespace App\Repositories\Contracts;

use App\Models\Tdr;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TdrRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Obtenir les TDRs d'un projet spécifique
     */
    public function getByProjetId(int $projetId): Collection;

    /**
     * Obtenir un TDR spécifique d'un projet par type
     */
    public function findByProjetAndType(int $projetId, string $type): ?Tdr;

    /**
     * Obtenir les TDRs par statut
     */
    public function getByStatut(string $statut): Collection;

    /**
     * Obtenir les TDRs par type (prefaisabilite/faisabilite)
     */
    public function getByType(string $type): Collection;

    /**
     * Obtenir les TDRs en attente d'évaluation
     */
    public function getEnAttenteEvaluation(): Collection;

    /**
     * Obtenir les TDRs évalués par un utilisateur
     */
    public function getEvaluesParUtilisateur(int $evaluateurId): Collection;

    /**
     * Obtenir les TDRs soumis entre deux dates
     */
    public function getSoumisBetween($dateDebut, $dateFin): Collection;

    /**
     * Rechercher des TDRs avec filtres
     */
    public function search(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Mettre à jour le statut d'un TDR
     */
    public function updateStatut(int $id, string $statut): bool;

    /**
     * Mettre à jour les statistiques d'évaluation
     */
    public function updateStatistiques(int $id, int $passe, int $retour, int $nonAccepte): bool;

    /**
     * Obtenir les TDRs avec leurs relations
     */
    public function getWithRelations(array $relations = []): Collection;

    /**
     * Obtenir les statistiques globales des TDRs
     */
    public function getStatistiques(): array;

    /**
     * Obtenir les TDRs avec fichiers
     */
    public function getAvecFichiers(): Collection;

    /**
     * Obtenir les TDRs avec commentaires récents
     */
    public function getAvecCommentairesRecents(int $jours = 7): Collection;

    /**
     * Attacher un fichier à un TDR
     */
    public function attacherFichier(int $tdrId, array $fichierData): bool;

    /**
     * Ajouter un commentaire à un TDR
     */
    public function ajouterCommentaire(int $tdrId, string $commentaire, int $commentateurId): bool;
}