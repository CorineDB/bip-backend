<?php

namespace App\Notifications;

use App\Models\Evaluation;
use App\Models\Projet;
use App\Models\Rapport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationRapportEvaluationExAnteValide extends Notification implements ShouldQueue
{
    use Queueable;

    protected Rapport $rapport;
    protected Projet $projet;
    protected Evaluation $evaluation;
    protected User $validateur;
    protected string $decision;
    protected string $typeDestinataire;
    protected string $validateurNomComplet;

    /**
     * Types de destinataires possibles :
     * - dpaf_supervision : DPAF du ministère (supervision)
     * - equipe_organisation : Équipe de l'organisation (félicitation)
     * - soumetteur_confirmation : Soumetteur du rapport (information)
     * - dgpd_info : DGPD (confirmation de la décision)
     */
    public function __construct(
        Rapport $rapport,
        Projet $projet,
        Evaluation $evaluation,
        User $validateur,
        string $decision,
        string $typeDestinataire
    ) {
        $this->rapport = $rapport;
        $this->projet = $projet;
        $this->evaluation = $evaluation;
        $this->validateur = $validateur;
        $this->decision = $decision;
        $this->typeDestinataire = $typeDestinataire;

        $this->validateurNomComplet = $this->validateur->personne->prenom . ' ' . $this->validateur->personne->nom;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database', 'broadcast'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->getSubject())
            ->greeting('Bonjour ' . $notifiable->personne->prenom . ' ' . $notifiable->personne->nom . ',')
            ->line($this->getMessage())
            ->line('**Projet :** ' . $this->projet->titre_projet)
            ->line('**Validé par :** ' . $this->validateurNomComplet)
            ->line('**Date de validation :** ' . $this->rapport->date_validation?->format('d/m/Y à H:i'))
            ->line('**Décision :** ' . $this->getDecisionLabel())
            ->line($this->getActionMessage())
            ->action($this->getActionText(), $this->getActionUrl())
            ->line('Merci pour votre engagement !');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rapport_evaluation_ex_ante_valide',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'rapport_id' => $this->rapport->hashed_id,
            'projet_id' => $this->projet->hashed_id,
            'evaluation_id' => $this->evaluation->hashed_id,
            'validateur_id' => $this->validateur->hashed_id,
            'validateur_name' => $this->validateurNomComplet,
            'decision' => $this->decision,
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'action_message' => $this->getActionMessage(),
            'priorite' => $this->getPriorite(),
            'metadata' => [
                'projet_titre' => $this->projet->titre_projet,
                'rapport_intitule' => $this->rapport->intitule,
                'date_validation' => $this->rapport->date_validation?->toDateTimeString(),
                'statut_rapport' => $this->rapport->statut,
                'organisation_nom' => $this->projet->organisation->nom ?? null,
                'ministere_nom' => $this->projet->ministere->nom ?? null,
            ],
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'rapport_evaluation_ex_ante_valide',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'rapport_id' => $this->rapport->hashed_id,
            'projet_id' => $this->projet->hashed_id,
            'evaluation_id' => $this->evaluation->hashed_id,
            'validateur_id' => $this->validateur->hashed_id,
            'validateur_name' => $this->validateurNomComplet,
            'decision' => $this->decision,
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'priorite' => $this->getPriorite(),
            'date_validation' => $this->rapport->date_validation?->toDateTimeString(),
        ]);
    }

    /**
     * Get subject based on recipient type.
     */
    protected function getSubject(): string
    {
        return match($this->typeDestinataire) {
            'dpaf_supervision' => $this->decision === 'valider'
                ? 'Validation : Rapport d\'évaluation ex-ante validé'
                : 'Information : Rapport d\'évaluation ex-ante à améliorer',
            'equipe_organisation' => $this->decision === 'valider'
                ? 'Félicitations : Votre rapport d\'évaluation ex-ante a été validé'
                : 'Action requise : Votre rapport d\'évaluation ex-ante nécessite des améliorations',
            'soumetteur_confirmation' => $this->decision === 'valider'
                ? 'Confirmation : Votre rapport a été validé avec succès'
                : 'Notification : Votre rapport nécessite des révisions',
            'dgpd_info' => $this->decision === 'valider'
                ? 'Confirmation : Rapport validé et projet transféré'
                : 'Confirmation : Rapport marqué à améliorer',
            default => 'Rapport d\'évaluation ex-ante évalué',
        };
    }

    /**
     * Get message based on recipient type.
     */
    protected function getMessage(): string
    {
        return match($this->typeDestinataire) {
            'dpaf_supervision' => $this->decision === 'valider'
                ? 'Le rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" a été validé par ' . $this->validateurNomComplet . '. Le projet est prêt pour la prochaine étape.'
                : 'Le rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" a été marqué à améliorer par ' . $this->validateurNomComplet . '.',
            'equipe_organisation' => $this->decision === 'valider'
                ? 'Félicitations ! Le rapport d\'évaluation ex-ante pour votre projet "' . $this->projet->titre_projet .
                  '" a été validé par la DGPD. Vous pouvez passer à la prochaine étape.'
                : 'Le rapport d\'évaluation ex-ante pour votre projet "' . $this->projet->titre_projet .
                  '" nécessite des améliorations. Veuillez consulter les commentaires et soumettre une version révisée.',
            'soumetteur_confirmation' => $this->decision === 'valider'
                ? 'Votre rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" a été validé par la DGPD.'
                : 'Votre rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" nécessite des révisions. Consultez les commentaires de la DGPD.',
            'dgpd_info' => $this->decision === 'valider'
                ? 'Vous avez validé le rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '". Le projet a été transféré vers le système externe.'
                : 'Vous avez marqué le rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" comme nécessitant des améliorations.',
            default => 'Le rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet . '" a été évalué.',
        };
    }

    /**
     * Get action URL based on recipient type.
     */
    protected function getActionUrl(): string
    {
        return match($this->typeDestinataire) {
            'dpaf_supervision', 'dgpd_info' => '/projets/' . $this->projet->hashed_id . '/rapports/' . $this->rapport->hashed_id,
            'equipe_organisation', 'soumetteur_confirmation' => $this->decision === 'valider'
                ? '/projets/' . $this->projet->hashed_id
                : '/projets/' . $this->projet->hashed_id . '/rapports/' . $this->rapport->hashed_id . '/ameliorer',
            default => '/projets/' . $this->projet->hashed_id,
        };
    }

    /**
     * Get action text based on recipient type.
     */
    protected function getActionText(): string
    {
        return match($this->typeDestinataire) {
            'dpaf_supervision', 'dgpd_info' => 'Consulter le rapport',
            'equipe_organisation', 'soumetteur_confirmation' => $this->decision === 'valider'
                ? 'Voir le projet'
                : 'Consulter les commentaires',
            default => 'Voir les détails',
        };
    }

    /**
     * Get action message based on recipient type.
     */
    protected function getActionMessage(): string
    {
        return match($this->typeDestinataire) {
            'dpaf_supervision' => $this->decision === 'valider'
                ? 'Le projet passe à la prochaine étape du processus.'
                : 'Le rapport sera resoumis après amélioration.',
            'equipe_organisation' => $this->decision === 'valider'
                ? 'Félicitations pour cette validation ! Vous pouvez continuer le processus.'
                : 'Veuillez consulter les commentaires et soumettre une version améliorée du rapport.',
            'soumetteur_confirmation' => $this->decision === 'valider'
                ? 'Le rapport a été accepté. Vous serez notifié(e) des prochaines étapes.'
                : 'Consultez les commentaires de la DGPD pour améliorer votre rapport.',
            'dgpd_info' => $this->decision === 'valider'
                ? 'Le projet a été synchronisé avec le système externe.'
                : 'L\'organisation doit soumettre une version améliorée.',
            default => 'Veuillez consulter le rapport pour plus de détails.',
        };
    }

    /**
     * Get priority level for the notification.
     */
    protected function getPriorite(): string
    {
        return match($this->typeDestinataire) {
            'equipe_organisation' => $this->decision === 'valider' ? 'haute' : 'haute',
            'dpaf_supervision' => 'moyenne',
            default => 'normale',
        };
    }

    /**
     * Get decision label for display.
     */
    protected function getDecisionLabel(): string
    {
        return match($this->decision) {
            'valider' => 'Validé',
            'ameliorer' => 'À améliorer',
            default => ucfirst($this->decision),
        };
    }
}
