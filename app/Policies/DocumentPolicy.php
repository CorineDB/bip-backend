<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DocumentPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir la liste
    }

    /**
     * Determine if the user can view the document.
     */
    public function view(User $user, Document $document): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir un document
    }

    /**
     * Determine if the user can create documents.
     */
    public function create(User $user): bool
    {
        // Uniquement le super admin peut créer la fiche idée
        // Pour les autres types de documents, on peut ajouter d'autres permissions
        return $user->role?->slug === 'super-admin';
    }

    /**
     * Determine if the user can update the document.
     */
    public function update(User $user, Document $document): bool
    {
        // Uniquement les personnes possédant la permission de modification
        return $user->hasPermission('modifier-document');
    }

    /**
     * Determine if the user can delete the document.
     */
    public function delete(User $user, Document $document): bool
    {
        // Vérifier si le document est de type protégé via sa catégorie
        $categorieSlug = $document->categorie?->slug;
        $protectedTypes = ['fiche-idee', 'note-conceptuelle'];
        
        if (in_array($categorieSlug, $protectedTypes)) {
            // Tout document de type "fiche-idee", "note-conceptuelle" ne devrait pas être supprimé peu importe qui c'est
            return false;
        }

        // Pour les autres documents, vérifier la permission de suppression
        return $user->hasPermission('supprimer-document');
    }

    /**
     * Determine if the user can restore the document.
     */
    public function restore(User $user, Document $document): bool
    {
        return $user->hasPermission('restaurer-document');
    }

    /**
     * Determine if the user can permanently delete the document.
     */
    public function forceDelete(User $user, Document $document): bool
    {
        // Les documents protégés ne peuvent jamais être supprimés définitivement
        $categorieSlug = $document->categorie?->slug;
        $protectedTypes = ['fiche-idee', 'note-conceptuelle'];
        
        if (in_array($categorieSlug, $protectedTypes)) {
            return false;
        }

        return $user->hasPermission('supprimer-definitivement-document');
    }
}