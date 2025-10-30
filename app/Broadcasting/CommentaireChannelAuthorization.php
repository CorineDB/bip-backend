<?php

namespace App\Broadcasting;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class CommentaireChannelAuthorization
{
    /**
     * Vérifier si un utilisateur peut accéder aux commentaires d'une ressource
     *
     * @param User $user
     * @param mixed $ressource
     * @return bool
     */
    public static function canAccess(User $user, $ressource): bool
    {
        $className = class_basename($ressource);

        try {
            switch ($className) {
                case 'Projet':
                    return self::canAccessProjet($user, $ressource);

                case 'NoteConceptuelle':
                    return self::canAccessNoteConceptuelle($user, $ressource);

                case 'Tdr':
                    return self::canAccessTdr($user, $ressource);

                case 'Evaluation':
                    return self::canAccessEvaluation($user, $ressource);

                case 'EvaluationChamp':
                    return self::canAccessEvaluationChamp($user, $ressource);

                case 'EvaluationCritere':
                    return self::canAccessEvaluationCritere($user, $ressource);

                case 'Rapport':
                    return self::canAccessRapport($user, $ressource);

                case 'Decision':
                    return self::canAccessDecision($user, $ressource);

                case 'IdeeProjet':
                    return self::canAccessIdeeProjet($user, $ressource);

                case 'Fichier':
                    return self::canAccessFichier($user, $ressource);

                case 'ChampProjet':
                    return self::canAccessChampProjet($user, $ressource);

                default:
                    Log::warning('Type de ressource non géré dans CommentaireChannelAuthorization', [
                        'type' => $className
                    ]);
                    return false;
            }
        } catch (\Exception $e) {
            Log::error('Erreur lors de la vérification des permissions de commentaire', [
                'user_id' => $user->id,
                'ressource_type' => $className,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    private static function canAccessProjet(User $user, $projet): bool
    {
        // DGPD ont accès à tous les projets
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès aux projets de leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            if ($projet->ideeProjet && $projet->ideeProjet->ministereId && $user->ministere?->id) {
                return $projet->ideeProjet->ministereId === $user->ministere?->id;
            }
        }

        // Responsable du projet
        if ($projet->ideeProjet && $projet->ideeProjet->responsableId === $user->id) {
            return true;
        }

        // TODO: Ajouter membres de l'équipe si relation existe
        // if ($projet->equipe && $projet->equipe->contains('user_id', $user->id)) {
        //     return true;
        // }

        return false;
    }

    private static function canAccessNoteConceptuelle(User $user, $note): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès aux notes de leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            if ($note->projet && $note->projet->ideeProjet && $note->projet->ideeProjet->ministereId && $user->ministere?->id) {
                return $note->projet->ideeProjet->ministereId === $user->ministere?->id;
            }
        }

        // Rédacteur de la note
        if (isset($note->rediger_par) && $note->rediger_par === $user->id) {
            return true;
        }

        // Responsable du projet associé
        if ($note->projet && $note->projet->ideeProjet && $note->projet->ideeProjet->responsableId === $user->id) {
            return true;
        }

        return false;
    }

    private static function canAccessTdr(User $user, $tdr): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès aux TDR de leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            if ($tdr->projet && $tdr->projet->ideeProjet && $tdr->projet->ideeProjet->ministereId && $user->ministere?->id) {
                return $tdr->projet->ideeProjet->ministereId === $user->ministere?->id;
            }
        }

        // Créateur du TDR
        if (isset($tdr->soumis_par_id) && $tdr->soumis_par_id === $user->id) {
            return true;
        }

        // Responsable du projet associé
        if ($tdr->projet && $tdr->projet->ideeProjet && $tdr->projet->ideeProjet->responsableId === $user->id) {
            return true;
        }

        return false;
    }

    private static function canAccessEvaluation(User $user, $evaluation): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès aux évaluations de leur ministère via la relation polymorphique
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            $projetable = $evaluation->projetable;
            if ($projetable && $user->ministere?->id) {
                // Si c'est un Projet
                if ($projetable instanceof \App\Models\Projet && $projetable->ideeProjet && $projetable->ideeProjet->ministereId) {
                    return $projetable->ideeProjet->ministereId === $user->ministere->id;
                }
                // Si c'est une NoteConceptuelle
                if ($projetable instanceof \App\Models\NoteConceptuelle && $projetable->projet && $projetable->projet->ideeProjet && $projetable->projet->ideeProjet->ministereId) {
                    return $projetable->projet->ideeProjet->ministereId === $user->ministere->id;
                }
                // Si c'est un TDR
                if ($projetable instanceof \App\Models\Tdr && $projetable->projet && $projetable->projet->ideeProjet && $projetable->projet->ideeProjet->ministereId) {
                    return $projetable->projet->ideeProjet->ministereId === $user->ministere->id;
                }
                // Si c'est une IdeeProjet (accès direct au ministereId)
                if ($projetable instanceof \App\Models\IdeeProjet && isset($projetable->ministereId)) {
                    return $projetable->ministereId === $user->ministere->id;
                }
                // Si c'est un Rapport
                if ($projetable instanceof \App\Models\Rapport && $projetable->projet && $projetable->projet->ideeProjet && $projetable->projet->ideeProjet->ministereId) {
                    return $projetable->projet->ideeProjet->ministereId === $user->ministere->id;
                }
            }
        }

        // Évaluateur
        if (isset($evaluation->evaluateur_id) && $evaluation->evaluateur_id === $user->id) {
            return true;
        }

        // Validateur
        if (isset($evaluation->valider_par) && $evaluation->valider_par === $user->id) {
            return true;
        }

        // Responsable via la relation polymorphique
        $projetable = $evaluation->projetable;
        if ($projetable) {
            // Si c'est un Projet
            if ($projetable instanceof \App\Models\Projet && $projetable->ideeProjet && $projetable->ideeProjet->responsableId === $user->id) {
                return true;
            }
            // Si c'est NoteConceptuelle ou TDR
            if (($projetable instanceof \App\Models\NoteConceptuelle || $projetable instanceof \App\Models\Tdr)
                && $projetable->projet && $projetable->projet->ideeProjet && $projetable->projet->ideeProjet->responsableId === $user->id) {
                return true;
            }
            // Si c'est une IdeeProjet
            if ($projetable instanceof \App\Models\IdeeProjet && isset($projetable->responsableId) && $projetable->responsableId === $user->id) {
                return true;
            }
            // Si c'est un Rapport
            if ($projetable instanceof \App\Models\Rapport && $projetable->projet && $projetable->projet->ideeProjet && $projetable->projet->ideeProjet->responsableId === $user->id) {
                return true;
            }
        }

        return false;
    }

    private static function canAccessEvaluationChamp(User $user, $evaluationChamp): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès via leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            if ($evaluationChamp->evaluation && $evaluationChamp->evaluation->projet
                && $evaluationChamp->evaluation->projet->ideeProjet
                && $evaluationChamp->evaluation->projet->ideeProjet->ministereId && $user->ministere?->id) {
                return $evaluationChamp->evaluation->projet->ideeProjet->ministereId === $user->ministere?->id;
            }
        }

        // Évaluateur via la relation evaluation
        if ($evaluationChamp->evaluation && $evaluationChamp->evaluation->evaluateur_id === $user->id) {
            return true;
        }

        // Validateur via la relation evaluation
        if ($evaluationChamp->evaluation && $evaluationChamp->evaluation->valider_par === $user->id) {
            return true;
        }

        return false;
    }

    private static function canAccessEvaluationCritere(User $user, $evaluationCritere): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès via leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            if ($evaluationCritere->evaluation && $evaluationCritere->evaluation->projet
                && $evaluationCritere->evaluation->projet->ideeProjet
                && $evaluationCritere->evaluation->projet->ideeProjet->ministereId && $user->ministere?->id) {
                return $evaluationCritere->evaluation->projet->ideeProjet->ministereId === $user->ministere?->id;
            }
        }

        // Évaluateur via la relation evaluation
        if ($evaluationCritere->evaluation && $evaluationCritere->evaluation->evaluateur_id === $user->id) {
            return true;
        }

        return false;
    }

    private static function canAccessRapport(User $user, $rapport): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès aux rapports de leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            if ($rapport->projet && $rapport->projet->ideeProjet && $rapport->projet->ideeProjet->ministereId && $user->ministere?->id) {
                return $rapport->projet->ideeProjet->ministereId === $user->ministere?->id;
            }
        }

        // Créateur du rapport
        if (isset($rapport->soumis_par_id) && $rapport->soumis_par_id === $user->id) {
            return true;
        }

        return false;
    }

    private static function canAccessDecision(User $user, $decision): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès aux décisions de leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            // TODO: Vérifier comment la décision est liée au ministère
            // Si via un projet: $decision->projet->ideeProjet->ministereId
        }

        // Validateur de la décision
        if (isset($decision->validator_id) && $decision->validator_id === $user->id) {
            return true;
        }

        return false;
    }

    private static function canAccessIdeeProjet(User $user, $idee): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès aux idées de leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            if (isset($idee->ministere_id) && $user->ministere?->id) {
                return $idee->ministere_id === $user->ministere?->id;
            }
        }

        // Responsable de l'idée
        if (isset($idee->responsableId) && $idee->responsableId === $user->id) {
            return true;
        }

        return false;
    }

    private static function canAccessFichier(User $user, $fichier): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès aux fichiers de leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            // TODO: Vérifier l'accès via la ressource parente (fichierAttachable)
            // qui peut avoir un ministere_id
        }

        // Uploader du fichier
        if (isset($fichier->uploaded_by) && $fichier->uploaded_by === $user->id) {
            return true;
        }

        return false;
    }

    private static function canAccessChampProjet(User $user, $champProjet): bool
    {
        // DGPD ont accès à tout
        if (in_array($user->type, ['dgpd']) || in_array($user->role, ['dgpd'])) {
            return true;
        }

        // DPAF ont accès aux champs de leur ministère
        if (in_array($user->type, ['dpaf']) || in_array($user->role, ['dpaf'])) {
            if ($champProjet->projetable && $champProjet->projetable->ideeProjet
                && $champProjet->projetable->ideeProjet->ministereId && $user->ministere?->id) {
                return $champProjet->projetable->ideeProjet->ministereId == $user->ministere?->id;
            }
        }

        // Responsable du projet associé
        if ($champProjet->projetable && $champProjet->projetable->ideeProjet && $champProjet->projetable->ideeProjet->responsableId === $user->id) {
            return true;
        }

        return false;
    }
}
