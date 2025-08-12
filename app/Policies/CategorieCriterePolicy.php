<?php

namespace App\Policies;

use App\Models\CategorieCritere;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategorieCriterePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any categories critere.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir la liste
    }

    /**
     * Determine if the user can view the categorie critere.
     */
    public function view(User $user, CategorieCritere $categorieCritere): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir une catégorie
    }

    /**
     * Determine if the user can create categories critere.
     */
    public function create(User $user): bool
    {
        // Uniquement le super admin ou une personne possédant la permission de création d'une catégorie de critère
        return $user->role?->slug === 'super-admin' || 
               $user->hasPermission('creer-categorie-critere');
    }

    /**
     * Determine if the user can update the categorie critere.
     */
    public function update(User $user, CategorieCritere $categorieCritere): bool
    {
        // Uniquement les personnes possédant la permission de modification
        return $user->hasPermission('modifier-categorie-critere');
    }

    /**
     * Determine if the user can delete the categorie critere.
     */
    public function delete(User $user, CategorieCritere $categorieCritere): bool
    {
        // Une catégorie de critère dont le slug est égal aux valeurs protégées ne devrait pas être supprimée
        $protectedSlugs = [
            'evaluation-preliminaire-multi-projet-impact-climatique',
            'grille-analyse-multi-critere'
        ];
        
        if (in_array($categorieCritere->slug, $protectedSlugs)) {
            return false;
        }

        // Pour les autres catégories, vérifier la permission de suppression
        return $user->hasPermission('supprimer-categorie-critere');
    }

    /**
     * Determine if the user can restore the categorie critere.
     */
    public function restore(User $user, CategorieCritere $categorieCritere): bool
    {
        return $user->hasPermission('restaurer-categorie-critere');
    }

    /**
     * Determine if the user can permanently delete the categorie critere.
     */
    public function forceDelete(User $user, CategorieCritere $categorieCritere): bool
    {
        // Les catégories protégées ne peuvent jamais être supprimées définitivement
        $protectedSlugs = [
            'evaluation-preliminaire-multi-projet-impact-climatique',
            'grille-analyse-multi-critere'
        ];
        
        if (in_array($categorieCritere->slug, $protectedSlugs)) {
            return false;
        }

        return $user->hasPermission('supprimer-definitivement-categorie-critere');
    }
}