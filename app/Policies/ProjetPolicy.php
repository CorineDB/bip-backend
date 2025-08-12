<?php

namespace App\Policies;

use App\Models\Projet;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any projets.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir la liste
    }

    /**
     * Determine if the user can view the projet.
     */
    public function view(User $user, Projet $projet): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir un projet
    }

    /**
     * Determine if the user can create projets.
     */
    public function create(User $user): bool
    {
        // Aucun projet ne devrait être créé par qui que ce soit
        return false;
    }

    /**
     * Determine if the user can update the projet.
     */
    public function update(User $user, Projet $projet): bool
    {
        // Aucun projet ne devrait être modifié par qui que ce soit
        return false;
    }

    /**
     * Determine if the user can delete the projet.
     */
    public function delete(User $user, Projet $projet): bool
    {
        // Aucun projet ne devrait être supprimé par qui que ce soit
        return false;
    }

    /**
     * Determine if the user can restore the projet.
     */
    public function restore(User $user, Projet $projet): bool
    {
        return false;
    }

    /**
     * Determine if the user can permanently delete the projet.
     */
    public function forceDelete(User $user, Projet $projet): bool
    {
        return false;
    }
}