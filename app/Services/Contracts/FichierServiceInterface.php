<?php

namespace App\Services\Contracts;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

interface FichierServiceInterface extends AbstractServiceInterface
{
    /**
     * Récupérer les fichiers accessibles à l'utilisateur groupés par dossier
     */
    public function getFichiersAccessibles(array $filters = []): JsonResponse;

    /**
     * Upload d'un fichier libre (sans ressource rattachée)
     */
    public function uploadFichierLibre(Request $request): JsonResponse;

    /**
     * Télécharger un fichier avec vérification des permissions
     */
    public function telechargerFichier(string $id): StreamedResponse;

    /**
     * Visualiser un fichier dans le navigateur
     */
    public function visualiserFichier(string $id): StreamedResponse;

    /**
     * Partager un fichier avec d'autres utilisateurs
     */
    public function partagerFichierAvecUtilisateurs(string $id, array $data): JsonResponse;

    /**
     * Supprimer un fichier (seulement les fichiers uploadés librement)
     */
    public function supprimerFichier(string $id): JsonResponse;

    /**
     * Obtenir les statistiques des fichiers de l'utilisateur
     */
    public function getStatistiquesUtilisateur(): JsonResponse;

    /**
     * Vérifier les permissions d'accès à un fichier
     */
    public function verifierPermissionsFichier(string $id, string $permission = 'view'): bool;

    /**
     * Obtenir un fichier avec vérification des permissions
     */
    public function getFichierAvecPermissions(string $id): ?object;

    /**
     * Incrémenter le compteur de téléchargements
     */
    public function incrementerTelechargements(string $id): void;

    /**
     * Incrémenter le compteur de vues
     */
    public function incrementerVues(string $id): void;
}
