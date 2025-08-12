<?php

namespace App\Policies;

use App\Models\CategorieDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategorieDocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any categories document.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir la liste
    }

    /**
     * Determine if the user can view the categorie document.
     */
    public function view(User $user, CategorieDocument $categorieDocument): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir une catégorie
    }

    /**
     * Determine if the user can create categories document.
     */
    public function create(User $user): bool
    {
        // Uniquement le super admin ou une personne possédant la permission de création d'une catégorie de document
        return $user->role?->slug === 'super-admin' || 
               $user->hasPermission('creer-categorie-document');
    }

    /**
     * Determine if the user can update the categorie document.
     */
    public function update(User $user, CategorieDocument $categorieDocument): bool
    {
        // Uniquement les personnes possédant la permission de modification
        return $user->hasPermission('modifier-categorie-document');
    }

    /**
     * Determine if the user can delete the categorie document.
     */
    public function delete(User $user, CategorieDocument $categorieDocument): bool
    {
        // Une catégorie de document dont le slug est égal à "fiche-idee", "note-conceptuelle" ne devrait pas être supprimée
        $protectedSlugs = ['fiche-idee', 'note-conceptuelle'];
        
        if (in_array($categorieDocument->slug, $protectedSlugs)) {
            return false;
        }

        // Pour les autres catégories, vérifier la permission de suppression
        return $user->hasPermission('supprimer-categorie-document');
    }

    /**
     * Determine if the user can restore the categorie document.
     */
    public function restore(User $user, CategorieDocument $categorieDocument): bool
    {
        return $user->hasPermission('restaurer-categorie-document');
    }

    /**
     * Determine if the user can permanently delete the categorie document.
     */
    public function forceDelete(User $user, CategorieDocument $categorieDocument): bool
    {
        // Les catégories protégées ne peuvent jamais être supprimées définitivement
        $protectedSlugs = ['fiche-idee', 'note-conceptuelle'];
        
        if (in_array($categorieDocument->slug, $protectedSlugs)) {
            return false;
        }

        return $user->hasPermission('supprimer-definitivement-categorie-document');
    }
}