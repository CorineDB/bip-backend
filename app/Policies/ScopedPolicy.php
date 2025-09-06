<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ScopedPolicy
{
    use HandlesAuthorization;

    /**
     * Policy de base pour les permissions scopées
     * Peut être héritée par les policies spécifiques (ProjetPolicy, TdrPolicy, etc.)
     */

    // Permissions TDR
    public function appreciateTdr(User $user, $tdr)
    {
        return $user->hasScopedPermission('appreciate_tdr', $tdr, $tdr->workflow_stage ?? 'submission');
    }

    public function validateTdr(User $user, $tdr)
    {
        return $user->hasScopedPermission('validate_tdr', $tdr, $tdr->workflow_stage ?? 'validation');
    }

    public function submitTdr(User $user, $tdr)
    {
        return $user->hasScopedPermission('submit_tdr', $tdr, 'submission');
    }

    // Permissions Rapport
    public function appreciateReport(User $user, $rapport)
    {
        return $user->hasScopedPermission('appreciate_report', $rapport, $rapport->workflow_stage ?? 'submission');
    }

    public function validateReport(User $user, $rapport)
    {
        return $user->hasScopedPermission('validate_report', $rapport, $rapport->workflow_stage ?? 'validation');
    }

    public function submitReport(User $user, $rapport)
    {
        return $user->hasScopedPermission('submit_report', $rapport, 'submission');
    }

    // Permissions Projet
    public function validateProject(User $user, $projet)
    {
        return $user->hasScopedPermission('validate_project', $projet, $projet->workflow_stage ?? 'validation');
    }

    public function createNoteConceptuelle(User $user, $projet)
    {
        return $user->hasScopedPermission('create_note_conceptuelle', $projet, 'draft');
    }

    public function validateStudyStage(User $user, $projet)
    {
        return $user->hasScopedPermission('validate_study_stage', $projet, 'etude');
    }

    // Permissions IdeeProjet
    public function validateIdea(User $user, $ideeProjet)
    {
        return $user->hasScopedPermission('validate_idea', $ideeProjet, 'validation');
    }

    public function evaluateIdea(User $user, $ideeProjet)
    {
        return $user->hasScopedPermission('evaluate_idea', $ideeProjet, 'evaluation');
    }

    // Méthode générique pour vérifier toute permission
    public function checkPermission(User $user, $object, string $permission, ?string $stage = null)
    {
        return $user->hasScopedPermission($permission, $object, $stage);
    }

    // Méthodes d'aide pour vérifier l'accès par organisation
    protected function hasMinistryAccess(User $user, $object): bool
    {
        if (!method_exists($object, 'getMinistry')) {
            return false;
        }

        $ministry = $object->getMinistry();
        if (!$ministry) {
            return false;
        }

        return $user->activePermissionScopes()
            ->where('scopeable_type', 'App\Models\Organisation')
            ->where('scopeable_id', $ministry->id)
            ->exists();
    }

    protected function hasSectorAccess(User $user, $object): bool
    {
        if (!method_exists($object, 'getSector')) {
            return false;
        }

        $sector = $object->getSector();
        if (!$sector) {
            return false;
        }

        return $user->activePermissionScopes()
            ->where('scopeable_type', 'App\Models\Secteur')
            ->where('scopeable_id', $sector->id)
            ->exists();
    }

    protected function hasCategoryAccess(User $user, $object): bool
    {
        if (!method_exists($object, 'getCategory')) {
            return false;
        }

        $category = $object->getCategory();
        if (!$category) {
            return false;
        }

        return $user->activePermissionScopes()
            ->where('scopeable_type', 'App\Models\CategorieProjet')
            ->where('scopeable_id', $category->id)
            ->exists();
    }
}