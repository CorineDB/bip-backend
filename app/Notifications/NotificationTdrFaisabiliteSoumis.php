<?php

namespace App\Notifications;

use App\Models\Projet;
use App\Models\Tdr;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationTdrFaisabiliteSoumis extends Notification implements ShouldQueue
{
    use Queueable;

    protected Tdr $tdr;
    protected Projet $projet;
    protected User $soumetteur;
    protected bool $estResoumission;
    protected string $typeDestinataire;
    protected string $soumetteurNomComplet;

    /**
     * Types de destinataires possibles :
     * - dgpd_evaluation : DGPD qui doit évaluer le TDR
     * - dpaf_supervision : DPAF du ministère (supervision)
     * - equipe_organisation : Équipe de l'organisation (information)
     * - soumetteur_confirmation : Soumetteur (confirmation de réception)
     */
    public function __construct(
        Tdr $tdr,
        Projet $projet,
        User $soumetteur,
        bool $estResoumission,
        string $typeDestinataire
    ) {
        $this->tdr = $tdr;
        $this->projet = $projet;
        $this->soumetteur = $soumetteur;
        $this->estResoumission = $estResoumission;
        $this->typeDestinataire = $typeDestinataire;
        $this->soumetteurNomComplet = $this->soumetteur->personne->prenom . ' ' . $this->soumetteur->personne->nom;
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
            ->line('**Soumis par :** ' . $this->soumetteurNomComplet)
            ->line('**Date de soumission :** ' . $this->tdr->date_soumission?->format('d/m/Y à H:i'))
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
            'type' => 'tdr_faisabilite_soumis',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'tdr_id' => $this->tdr->hashed_id,
            'projet_id' => $this->projet->hashed_id,
            'soumetteur_id' => $this->soumetteur->hashed_id,
            'soumetteur_name' => $this->soumetteurNomComplet,
            'est_resoumission' => $this->estResoumission,
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'action_message' => $this->getActionMessage(),
            'priorite' => $this->getPriorite(),
            'metadata' => [
                'projet_titre' => $this->projet->titre_projet,
                'tdr_resume' => $this->tdr->resume,
                'date_soumission' => $this->tdr->date_soumission?->toDateTimeString(),
                'statut_tdr' => $this->tdr->statut,
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
            'type' => 'tdr_faisabilite_soumis',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'tdr_id' => $this->tdr->hashed_id,
            'projet_id' => $this->projet->hashed_id,
            'soumetteur_id' => $this->soumetteur->hashed_id,
            'soumetteur_name' => $this->soumetteurNomComplet,
            'est_resoumission' => $this->estResoumission,
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'priorite' => $this->getPriorite(),
            'date_soumission' => $this->tdr->date_soumission?->toDateTimeString(),
        ]);
    }

    /**
     * Get subject based on recipient type.
     */
    protected function getSubject(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_evaluation' => $this->estResoumission
                ? 'Resoumission : TDR de faisabilité à évaluer'
                : 'Nouveau TDR de faisabilité à évaluer',
            'dpaf_supervision' => $this->estResoumission
                ? 'Supervision : TDR de faisabilité resoumis'
                : 'Supervision : Nouveau TDR de faisabilité',
            'equipe_organisation' => $this->estResoumission
                ? 'Mise à jour : TDR de faisabilité resoumis'
                : 'Information : TDR de faisabilité soumis',
            'soumetteur_confirmation' => $this->estResoumission
                ? 'Confirmation : Votre TDR révisé a été resoumis'
                : 'Confirmation : Votre TDR a été soumis avec succès',
            default => 'TDR de faisabilité soumis',
        };
    }

    /**
     * Get message based on recipient type.
     */
    protected function getMessage(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_evaluation' => $this->estResoumission
                ? 'Le TDR de faisabilité pour le projet "' . $this->projet->titre_projet .
                  '" a été resoumis après révision par ' . $this->soumetteurNomComplet .
                  '. Veuillez procéder à son évaluation.'
                : 'Un nouveau TDR de faisabilité pour le projet "' . $this->projet->titre_projet .
                  '" a été soumis par ' . $this->soumetteurNomComplet . '. Veuillez procéder à son évaluation.',
            'dpaf_supervision' => $this->estResoumission
                ? 'Le TDR de faisabilité pour le projet "' . $this->projet->titre_projet .
                  '" a été resoumis après révision. Évaluation DGPD en attente.'
                : 'Un nouveau TDR de faisabilité pour le projet "' . $this->projet->titre_projet .
                  '" a été soumis. Évaluation DGPD en attente.',
            'equipe_organisation' => $this->estResoumission
                ? 'Le TDR de faisabilité pour votre projet "' . $this->projet->titre_projet .
                  '" a été resoumis après révision et est en cours d\'évaluation.'
                : 'Le TDR de faisabilité pour votre projet "' . $this->projet->titre_projet .
                  '" a été soumis et est en cours d\'évaluation.',
            'soumetteur_confirmation' => $this->estResoumission
                ? 'Votre TDR de faisabilité révisé pour le projet "' . $this->projet->titre_projet .
                  '" a été resoumis avec succès. Il sera évalué par la DGPD.'
                : 'Votre TDR de faisabilité pour le projet "' . $this->projet->titre_projet .
                  '" a été soumis avec succès. Il sera évalué par la DGPD.',
            default => 'Le TDR de faisabilité pour le projet "' . $this->projet->titre_projet . '" a été soumis.',
        };
    }

    /**
     * Get action URL based on recipient type.
     */
    protected function getActionUrl(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_evaluation' => '/projets/' . $this->projet->hashed_id . '/evaluation-tdr-prefaisabilite',
            'dpaf_supervision', 'equipe_organisation' => '/projets/' . $this->projet->hashed_id . '/tdr/' . $this->tdr->hashed_id,
            'soumetteur_confirmation' => '/projets/' . $this->projet->hashed_id,
            default => '/projets/' . $this->projet->hashed_id,
        };
    }

    /**
     * Get action text based on recipient type.
     */
    protected function getActionText(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_evaluation' => 'Évaluer le TDR',
            'dpaf_supervision', 'equipe_organisation' => 'Consulter le TDR',
            'soumetteur_confirmation' => 'Voir le projet',
            default => 'Voir les détails',
        };
    }

    /**
     * Get action message based on recipient type.->ha
     */
    protected function getActionMessage(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_evaluation' => $this->estResoumission
                ? 'Veuillez évaluer ce TDR révisé pour déterminer les prochaines étapes.'
                : 'Veuillez évaluer ce TDR pour déterminer les prochaines étapes.',
            'dpaf_supervision' => 'Le TDR est en cours d\'évaluation par la DGPD.',
            'equipe_organisation' => 'Vous serez notifié(e) une fois l\'évaluation terminée.',
            'soumetteur_confirmation' => 'Vous serez notifié(e) une fois l\'évaluation effectuée par la DGPD.',
            default => 'Veuillez consulter le TDR pour plus de détails.',
        };
    }

    /**
     * Get priority level for the notification.
     */
    protected function getPriorite(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_evaluation' => 'haute',
            'dpaf_supervision' => 'moyenne',
            default => 'normale',
        };
    }
}
