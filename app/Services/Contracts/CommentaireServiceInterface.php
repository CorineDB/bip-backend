<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface CommentaireServiceInterface extends AbstractServiceInterface
{
    /**
     * Créer un commentaire avec possibilité d'attacher des fichiers
     *
     * @param array $data - Doit contenir:
     *   - commentaire: string (obligatoire)
     *   - commentaireable_type: string (obligatoire)
     *   - commentaireable_id: int (obligatoire)
     *   - commentaire_id: int (optionnel - pour les réponses)
     *   - fichiers: array (optionnel - fichiers uploadés)
     * @return JsonResponse
     */
    public function create(array $data): JsonResponse;

    /**
     * Mettre à jour un commentaire avec possibilité de gérer les fichiers
     *
     * @param int|string $id
     * @param array $data - Peut contenir:
     *   - commentaire: string (optionnel)
     *   - fichiers: array (optionnel - nouveaux fichiers à ajouter)
     *   - fichiers_a_supprimer: array (optionnel - IDs des fichiers à supprimer)
     * @return JsonResponse
     */
    public function update(int|string $id, array $data): JsonResponse;

    /**
     * Supprimer un commentaire et ses fichiers attachés
     *
     * @param int|string $id
     * @return JsonResponse
     */
    public function delete(int|string $id): JsonResponse;

    /**
     * Récupérer tous les commentaires d'une ressource avec leurs fichiers
     *
     * @param string $resourceType
     * @param int $resourceId
     * @return JsonResponse
     */
    public function getCommentairesParRessource(string $resourceType, int $resourceId): JsonResponse;
}
