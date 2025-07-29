<?php

namespace App\Services\Contracts;

use Illuminate\Http\JsonResponse;

interface GroupeUtilisateurServiceInterface extends AbstractServiceInterface
{
    /**
     * Assigner des rôles à un groupe d'utilisateurs.
     *
     * @param int|string $groupeId
     * @param array $rolesIds
     * @return JsonResponse
     */
    public function assignRoles(int|string $groupeId, array $rolesIds): JsonResponse;

    /**
     * Retirer des rôles d'un groupe d'utilisateurs.
     *
     * @param int|string $groupeId
     * @param array $rolesIds
     * @return JsonResponse
     */
    public function detachRoles(int|string $groupeId, array $rolesIds): JsonResponse;

    /**
     * Ajouter des utilisateurs à un groupe.
     *
     * @param int|string $groupeId
     * @param array $usersIds
     * @return JsonResponse
     */
    public function addUsers(int|string $groupeId, array $usersIds): JsonResponse;

    /**
     * Retirer des utilisateurs d'un groupe.
     *
     * @param int|string $groupeId
     * @param array $usersIds
     * @return JsonResponse
     */
    public function removeUsers(int|string $groupeId, array $usersIds): JsonResponse;

    /**
     * Obtenir les rôles d'un groupe.
     *
     * @param int|string $groupeId
     * @return JsonResponse
     */
    public function getGroupRoles(int|string $groupeId): JsonResponse;

    /**
     * Obtenir les utilisateurs d'un groupe.
     *
     * @param int|string $groupeId
     * @return JsonResponse
     */
    public function getGroupUsers(int|string $groupeId): JsonResponse;

    /**
     * Créer un utilisateur et l'ajouter à un groupe.
     *
     * @param int|string $groupeId
     * @param array $userData
     * @return JsonResponse
     */
    public function createUserInGroup(int|string $groupeId, array $userData): JsonResponse;
}