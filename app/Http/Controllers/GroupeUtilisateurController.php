<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\groupes_utilisateur\StoreGroupeUtilisateurRequest;
use App\Http\Requests\groupes_utilisateur\UpdateGroupeUtilisateurRequest;
use App\Http\Requests\groupes_utilisateur\AddUsersRequest;
use App\Http\Requests\groupes_utilisateur\AssignRolesRequest;
use App\Http\Requests\groupes_utilisateur\CreateUserInGroupRequest;
use App\Services\Contracts\GroupeUtilisateurServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GroupeUtilisateurController extends Controller
{
    protected GroupeUtilisateurServiceInterface $service;

    public function __construct(GroupeUtilisateurServiceInterface $service)
    {
        $this->service = $service;
    }

    public function index(): JsonResponse
    {
        return $this->service->all();
    }

    public function show($id): JsonResponse
    {
        return $this->service->find($id);
    }

    public function store(StoreGroupeUtilisateurRequest $request): JsonResponse
    {
        return $this->service->create($request->all());
    }

    public function update(UpdateGroupeUtilisateurRequest $request, $id): JsonResponse
    {
        return $this->service->update($id, $request->all());
    }

    public function destroy($id): JsonResponse
    {
        return $this->service->delete($id);
    }

    /**
     * Assigner des rôles à un groupe d'utilisateurs
     */
    public function assignRoles(AssignRolesRequest $request, $id): JsonResponse
    {
        return $this->service->assignRoles($id, $request->validated()['roles']);
    }

    /**
     * Retirer des rôles d'un groupe d'utilisateurs
     */
    public function detachRoles(Request $request, $id): JsonResponse
    {
        return $this->service->detachRoles($id, $request->input('roles', []));
    }

    /**
     * Assigner des permissions à un groupe d'utilisateurs
     */
    public function assignPermissions(AssignRolesRequest $request, $id): JsonResponse
    {
        return $this->service->assignPermissions($id, $request->validated()['permissions']);
    }

    /**
     * Retirer des permissions d'un groupe d'utilisateurs
     */
    public function detachPermissions(Request $request, $id): JsonResponse
    {
        return $this->service->detachPermissions($id, $request->input('permissions', []));
    }

    /**
     * Ajouter des utilisateurs à un groupe
     */
    public function addUsers(AddUsersRequest $request, $id): JsonResponse
    {
        return $this->service->addUsers($id, $request->validated()['users']);
    }

    /**
     * Retirer des utilisateurs d'un groupe
     */
    public function removeUsers(Request $request, $id): JsonResponse
    {
        return $this->service->removeUsers($id, $request->input('users', []));
    }

    /**
     * Obtenir les rôles d'un groupe
     */
    public function getRoles($id): JsonResponse
    {
        return $this->service->getGroupRoles($id);
    }

    /**
     * Obtenir les utilisateurs d'un groupe
     */
    public function getUsers($id): JsonResponse
    {
        return $this->service->getGroupUsers($id);
    }

    /**
     * Créer un utilisateur et l'ajouter à un groupe
     */
    public function createUserInGroup(CreateUserInGroupRequest $request, $id): JsonResponse
    {
        return $this->service->createUserInGroup($id, $request->validated());
    }
}