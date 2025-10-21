<?php

namespace App\Notifications;

use App\Models\Projet;
use App\Models\Rapport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationRapportEvaluationExAnteSoumis extends Notification implements ShouldQueue
{
    use Queueable;

    protected Rapport $rapport;
    protected Projet $projet;
    protected User $soumetteur;
    protected bool $estResoumission;
    protected string $typeDestinataire;

    /**
     * Types de destinataires possibles :
     * - dgpd_validation : DGPD qui doit valider le rapport
     * - dpaf_supervision : DPAF du ministère (supervision)
     * - equipe_organisation : Équipe de l'organisation (information)
     * - soumetteur_confirmation : Soumetteur (confirmation de réception)
     */
    public function __construct(
        Rapport $rapport,
        Projet $projet,
        User $soumetteur,
        bool $estResoumission,
        string $typeDestinataire
    ) {
        $this->rapport = $rapport;
        $this->projet = $projet;
        $this->soumetteur = $soumetteur;
        $this->estResoumission = $estResoumission;
        $this->typeDestinataire = $typeDestinataire;
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
            ->greeting('Bonjour ' . $notifiable->name . ',')
            ->line($this->getMessage())
            ->line('**Projet :** ' . $this->projet->titre_projet)
            ->line('**Soumis par :** ' . $this->soumetteur->name)
            ->line('**Date de soumission :** ' . $this->rapport->date_soumission?->format('d/m/Y à H:i'))
            ->when($this->estResoumission, function ($mail) {
                return $mail->line('**Type :** Resoumission après révision');
            })
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
            'type' => 'rapport_evaluation_ex_ante_soumis',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'rapport_id' => $this->rapport->id,
            'projet_id' => $this->projet->id,
            'soumetteur_id' => $this->soumetteur->id,
            'soumetteur_name' => $this->soumetteur->name,
            'est_resoumission' => $this->estResoumission,
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'action_message' => $this->getActionMessage(),
            'priorite' => $this->getPriorite(),
            'metadata' => [
                'projet_titre' => $this->projet->titre_projet,
                'rapport_intitule' => $this->rapport->intitule,
                'date_soumission' => $this->rapport->date_soumission?->toDateTimeString(),
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
            'type' => 'rapport_evaluation_ex_ante_soumis',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'rapport_id' => $this->rapport->id,
            'projet_id' => $this->projet->id,
            'soumetteur_id' => $this->soumetteur->id,
            'soumetteur_name' => $this->soumetteur->name,
            'est_resoumission' => $this->estResoumission,
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'priorite' => $this->getPriorite(),
            'date_soumission' => $this->rapport->date_soumission?->toDateTimeString(),
        ]);
    }

    /**
     * Get subject based on recipient type.
     */
    protected function getSubject(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_validation' => $this->estResoumission
                ? 'Resoumission : Rapport d\'évaluation ex-ante à valider'
                : 'Nouveau rapport d\'évaluation ex-ante à valider',
            'dpaf_supervision' => $this->estResoumission
                ? 'Supervision : Rapport d\'évaluation ex-ante resoumis'
                : 'Supervision : Nouveau rapport d\'évaluation ex-ante',
            'equipe_organisation' => $this->estResoumission
                ? 'Mise à jour : Rapport d\'évaluation ex-ante resoumis'
                : 'Information : Rapport d\'évaluation ex-ante soumis',
            'soumetteur_confirmation' => $this->estResoumission
                ? 'Confirmation : Votre rapport révisé a été resoumis'
                : 'Confirmation : Votre rapport a été soumis avec succès',
            default => 'Rapport d\'évaluation ex-ante soumis',
        };
    }

    /**
     * Get message based on recipient type.
     */
    protected function getMessage(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_validation' => $this->estResoumission
                ? 'Le rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" a été resoumis après révision par ' . $this->soumetteur->name .
                  '. Veuillez procéder à son évaluation.'
                : 'Un nouveau rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" a été soumis par ' . $this->soumetteur->name . '. Veuillez procéder à son évaluation.',
            'dpaf_supervision' => $this->estResoumission
                ? 'Le rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" a été resoumis après révision. Décision DGPD en attente.'
                : 'Un nouveau rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" a été soumis. Décision DGPD en attente.',
            'equipe_organisation' => $this->estResoumission
                ? 'Le rapport d\'évaluation ex-ante pour votre projet "' . $this->projet->titre_projet .
                  '" a été resoumis après révision et est en cours d\'évaluation.'
                : 'Le rapport d\'évaluation ex-ante pour votre projet "' . $this->projet->titre_projet .
                  '" a été soumis et est en cours d\'évaluation.',
            'soumetteur_confirmation' => $this->estResoumission
                ? 'Votre rapport d\'évaluation ex-ante révisé pour le projet "' . $this->projet->titre_projet .
                  '" a été resoumis avec succès. Il sera évalué par la DGPD.'
                : 'Votre rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet .
                  '" a été soumis avec succès. Il sera évalué par la DGPD.',
            default => 'Le rapport d\'évaluation ex-ante pour le projet "' . $this->projet->titre_projet . '" a été soumis.',
        };
    }

    /**
     * Get action URL based on recipient type.
     */
    protected function getActionUrl(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_validation' => '/projets/' . $this->projet->id . '/validation-rapport-evaluation-ex-ante',
            'dpaf_supervision', 'equipe_organisation' => '/projets/' . $this->projet->id . '/rapports/' . $this->rapport->id,
            'soumetteur_confirmation' => '/projets/' . $this->projet->id,
            default => '/projets/' . $this->projet->id,
        };
    }

    /**
     * Get action text based on recipient type.
     */
    protected function getActionText(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_validation' => 'Évaluer le rapport',
            'dpaf_supervision', 'equipe_organisation' => 'Consulter le rapport',
            'soumetteur_confirmation' => 'Voir le projet',
            default => 'Voir les détails',
        };
    }

    /**
     * Get action message based on recipient type.
     */
    protected function getActionMessage(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_validation' => $this->estResoumission
                ? 'Veuillez évaluer ce rapport révisé pour déterminer les prochaines étapes du projet.'
                : 'Veuillez évaluer ce rapport pour déterminer les prochaines étapes du projet.',
            'dpaf_supervision' => 'Le rapport est en cours d\'évaluation par la DGPD.',
            'equipe_organisation' => 'Vous serez notifié(e) une fois l\'évaluation terminée.',
            'soumetteur_confirmation' => 'Vous serez notifié(e) une fois l\'évaluation effectuée par la DGPD.',
            default => 'Veuillez consulter le rapport pour plus de détails.',
        };
    }

    /**
     * Get priority level for the notification.
     */
    protected function getPriorite(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_validation' => 'haute',
            'dpaf_supervision' => 'moyenne',
            default => 'normale',
        };
    }
}
