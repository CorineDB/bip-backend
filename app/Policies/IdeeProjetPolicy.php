<?php

namespace App\Policies;

use App\Models\IdeeProjet;
use App\Models\User;
use App\Enums\StatutIdee;
use Illuminate\Auth\Access\HandlesAuthorization;

class IdeeProjetPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any idee projets.
     */
    public function viewAny(User $user): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir la liste
    }

    /**
     * Determine if the user can view the idee projet.
     */
    public function view(User $user, IdeeProjet $ideeProjet): bool
    {
        return true; // Tous les utilisateurs authentifiés peuvent voir une idée
    }

    /**
     * Determine if the user can create idee projets.
     */
    public function create(User $user): bool
    {
        // Uniquement le responsable projet ou toute personne possédant la permission de création d'une idée
        return $user->hasPermission('creer-idee-projet') || $user->role?->slug === 'responsable-projet';
    }

    /**
     * Determine if the user can update the idee projet.
     */
    public function update(User $user, IdeeProjet $ideeProjet): bool
    {
        // Uniquement la personne ayant créé l'idée de projet ET ayant la permission de modification
        return ($user->id === $ideeProjet->responsableId) && 
               $user->hasPermission('modifier-idee-projet');
    }

    /**
     * Determine if the user can delete the idee projet.
     */
    public function delete(User $user, IdeeProjet $ideeProjet): bool
    {
        // Vérifier que l'utilisateur est le créateur ET a la permission de suppression
        if ($user->id !== $ideeProjet->responsableId || !$user->hasPermission('supprimer-idee-projet')) {
            return false;
        }

        // Vérifier que le statut est à brouillon
        if ($ideeProjet->statut !== StatutIdee::BROUILLON) {
            return false;
        }

        // Vérifier qu'il n'y a pas encore d'évaluation climatique effectuée
        $hasEvaluationClimatique = $ideeProjet->evaluations()
            ->whereHas('categorieCritere', function ($query) {
                $query->where('slug', 'evaluation-preliminaire-multi-projet-impact-climatique');
            })
            ->exists();

        return !$hasEvaluationClimatique;
    }

    /**
     * Determine if the user can restore the idee projet.
     */
    public function restore(User $user, IdeeProjet $ideeProjet): bool
    {
        return $user->hasPermission('restaurer-idee-projet');
    }

    /**
     * Determine if the user can permanently delete the idee projet.
     */
    public function forceDelete(User $user, IdeeProjet $ideeProjet): bool
    {
        return $user->hasPermission('supprimer-definitivement-idee-projet');
    }
}