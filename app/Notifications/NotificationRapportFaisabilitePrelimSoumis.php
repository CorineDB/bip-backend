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

class NotificationRapportFaisabilitePrelimSoumis extends Notification implements ShouldQueue
{
    use Queueable;

    protected Rapport $rapport;
    protected Projet $projet;
    protected User $soumetteur;
    protected bool $estResoumission;
    protected string $typeDestinataire;
    protected string $soumetteurNomComplet;

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
            'type' => 'rapport_faisabilite_prelim_soumis',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'rapport_id' => $this->rapport->hashed_id,
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
            'type' => 'rapport_faisabilite_prelim_soumis',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'rapport_id' => $this->rapport->hashed_id,
            'projet_id' => $this->projet->hashed_id,
            'soumetteur_id' => $this->soumetteur->hashed_id,
            'soumetteur_name' => $this->soumetteurNomComplet,
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
                ? 'Resoumission : Rapport de faisabilité préliminaire à valider'
                : 'Nouveau rapport de faisabilité préliminaire à valider',
            'dpaf_supervision' => $this->estResoumission
                ? 'Supervision : Rapport de faisabilité préliminaire resoumis'
                : 'Supervision : Nouveau rapport de faisabilité préliminaire',
            'equipe_organisation' => $this->estResoumission
                ? 'Mise à jour : Rapport de faisabilité préliminaire resoumis'
                : 'Information : Rapport de faisabilité préliminaire soumis',
            'soumetteur_confirmation' => $this->estResoumission
                ? 'Confirmation : Votre rapport révisé a été resoumis'
                : 'Confirmation : Votre rapport a été soumis avec succès',
            default => 'Rapport de faisabilité préliminaire soumis',
        };
    }

    /**
     * Get message based on recipient type.
     */
    protected function getMessage(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_validation' => $this->estResoumission
                ? 'Le rapport de faisabilité préliminaire pour le projet "' . $this->projet->titre_projet .
                  '" a été resoumis après révision par ' . $this->soumetteurNomComplet .
                  '. Veuillez procéder à son évaluation.'
                : 'Un nouveau rapport de faisabilité préliminaire pour le projet "' . $this->projet->titre_projet .
                  '" a été soumis par ' . $this->soumetteurNomComplet . '. Veuillez procéder à son évaluation.',
            'dpaf_supervision' => $this->estResoumission
                ? 'Le rapport de faisabilité préliminaire pour le projet "' . $this->projet->titre_projet .
                  '" a été resoumis après révision. Décision DGPD en attente.'
                : 'Un nouveau rapport de faisabilité préliminaire pour le projet "' . $this->projet->titre_projet .
                  '" a été soumis. Décision DGPD en attente.',
            'equipe_organisation' => $this->estResoumission
                ? 'Le rapport de faisabilité préliminaire pour votre projet "' . $this->projet->titre_projet .
                  '" a été resoumis après révision et est en cours d\'évaluation.'
                : 'Le rapport de faisabilité préliminaire pour votre projet "' . $this->projet->titre_projet .
                  '" a été soumis et est en cours d\'évaluation.',
            'soumetteur_confirmation' => $this->estResoumission
                ? 'Votre rapport de faisabilité préliminaire révisé pour le projet "' . $this->projet->titre_projet .
                  '" a été resoumis avec succès. Il sera évalué par la DGPD.'
                : 'Votre rapport de faisabilité préliminaire pour le projet "' . $this->projet->titre_projet .
                  '" a été soumis avec succès. Il sera évalué par la DGPD.',
            default => 'Le rapport de faisabilité préliminaire pour le projet "' . $this->projet->titre_projet . '" a été soumis.',
        };
    }

    /**
     * Get action URL based on recipient type.
     */
    protected function getActionUrl(): string
    {
        return match($this->typeDestinataire) {
            'dgpd_validation' => '/projets/' . $this->projet->hashed_id . '/validation-rapport-faisabilite-prelim',
            'dpaf_supervision', 'equipe_organisation' => '/projets/' . $this->projet->hashed_id . '/rapports/' . $this->rapport->hashed_id,
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
