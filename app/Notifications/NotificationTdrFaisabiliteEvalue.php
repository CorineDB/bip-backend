<?php

namespace App\Notifications;

use App\Models\Evaluation;
use App\Models\Projet;
use App\Models\Tdr;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationTdrFaisabiliteEvalue extends Notification implements ShouldQueue
{
    use Queueable;

    protected Tdr $tdr;
    protected Projet $projet;
    protected Evaluation $evaluation;
    protected User $evaluateur;
    protected array $resultatsEvaluation;
    protected string $typeDestinataire;

    /**
     * Types de destinataires possibles :
     * - redacteur_resultat : Rédacteur du TDR (résultat de l'évaluation)
     * - dpaf_supervision : DPAF du ministère (supervision)
     * - equipe_organisation : Équipe de l'organisation (information)
     * - evaluateur_confirmation : Évaluateur (confirmation)
     */
    public function __construct(
        Tdr $tdr,
        Projet $projet,
        Evaluation $evaluation,
        User $evaluateur,
        array $resultatsEvaluation,
        string $typeDestinataire
    ) {
        $this->tdr = $tdr;
        $this->projet = $projet;
        $this->evaluation = $evaluation;
        $this->evaluateur = $evaluateur;
        $this->resultatsEvaluation = $resultatsEvaluation;
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
            ->line('**Évalué par :** ' . $this->evaluateur->name)
            ->line('**Résultat :** ' . $this->getResultatLabel())
            ->when(isset($this->resultatsEvaluation['message_resultat']), function ($mail) {
                return $mail->line('**Observation :** ' . $this->resultatsEvaluation['message_resultat']);
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
            'type' => 'tdr_faisabilite_evalue',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'tdr_id' => $this->tdr->id,
            'projet_id' => $this->projet->id,
            'evaluation_id' => $this->evaluation->id,
            'evaluateur_id' => $this->evaluateur->id,
            'evaluateur_name' => $this->evaluateur->name,
            'resultat_global' => $this->resultatsEvaluation['resultat_global'] ?? null,
            'resultat_label' => $this->getResultatLabel(),
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'action_message' => $this->getActionMessage(),
            'priorite' => $this->getPriorite(),
            'metadata' => [
                'projet_titre' => $this->projet->titre_projet,
                'tdr_resume' => $this->tdr->resume,
                'date_evaluation' => now()->toDateTimeString(),
                'organisation_nom' => $this->projet->organisation->nom ?? null,
                'ministere_nom' => $this->projet->ministere->nom ?? null,
                'resultats_evaluation' => $this->resultatsEvaluation,
            ],
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'tdr_faisabilite_evalue',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'tdr_id' => $this->tdr->id,
            'projet_id' => $this->projet->id,
            'evaluation_id' => $this->evaluation->id,
            'evaluateur_id' => $this->evaluateur->id,
            'evaluateur_name' => $this->evaluateur->name,
            'resultat_global' => $this->resultatsEvaluation['resultat_global'] ?? null,
            'resultat_label' => $this->getResultatLabel(),
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'priorite' => $this->getPriorite(),
            'date_evaluation' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get subject based on recipient type.
     */
    protected function getSubject(): string
    {
        $resultat = $this->resultatsEvaluation['resultat_global'] ?? 'evalue';

        return match($this->typeDestinataire) {
            'redacteur_resultat' => match($resultat) {
                'accepte' => 'TDR de faisabilité accepté',
                'accepte_sous_reserve' => 'TDR de faisabilité accepté sous réserve',
                'travail_supplementaire' => 'TDR de faisabilité : travail supplémentaire requis',
                'refuse' => 'TDR de faisabilité refusé',
                default => 'Résultat de l\'évaluation du TDR de faisabilité',
            },
            'dpaf_supervision' => 'Supervision : TDR de faisabilité évalué',
            'equipe_organisation' => 'Mise à jour : TDR de faisabilité évalué',
            'evaluateur_confirmation' => 'Confirmation : Évaluation du TDR enregistrée',
            default => 'TDR de faisabilité évalué',
        };
    }

    /**
     * Get message based on recipient type.
     */
    protected function getMessage(): string
    {
        $resultat = $this->getResultatLabel();

        return match($this->typeDestinataire) {
            'redacteur_resultat' =>
                'L\'évaluation de votre TDR de faisabilité pour le projet "' . $this->projet->titre_projet .
                '" a été effectuée par ' . $this->evaluateur->name . '. Résultat : ' . $resultat . '.',
            'dpaf_supervision' =>
                'Le TDR de faisabilité pour le projet "' . $this->projet->titre_projet .
                '" a été évalué. Résultat : ' . $resultat . '.',
            'equipe_organisation' =>
                'Le TDR de faisabilité pour votre projet "' . $this->projet->titre_projet .
                '" a été évalué. Résultat : ' . $resultat . '.',
            'evaluateur_confirmation' =>
                'Votre évaluation du TDR de faisabilité pour le projet "' . $this->projet->titre_projet .
                '" a été enregistrée avec succès.',
            default =>
                'Le TDR de faisabilité pour le projet "' . $this->projet->titre_projet . '" a été évalué.',
        };
    }

    /**
     * Get action URL based on recipient type.
     */
    protected function getActionUrl(): string
    {
        return match($this->typeDestinataire) {
            'redacteur_resultat', 'equipe_organisation' => '/projets/' . $this->projet->id . '/tdr/' . $this->tdr->id . '/evaluation',
            'dpaf_supervision' => '/projets/' . $this->projet->id . '/supervision',
            'evaluateur_confirmation' => '/projets/' . $this->projet->id . '/evaluations',
            default => '/projets/' . $this->projet->id,
        };
    }

    /**
     * Get action text based on recipient type.
     */
    protected function getActionText(): string
    {
        return match($this->typeDestinataire) {
            'redacteur_resultat', 'equipe_organisation' => 'Voir l\'évaluation',
            'dpaf_supervision' => 'Consulter la supervision',
            'evaluateur_confirmation' => 'Voir mes évaluations',
            default => 'Voir le projet',
        };
    }

    /**
     * Get action message based on recipient type.
     */
    protected function getActionMessage(): string
    {
        $resultat = $this->resultatsEvaluation['resultat_global'] ?? null;

        return match($this->typeDestinataire) {
            'redacteur_resultat' => match($resultat) {
                'accepte' => 'Prochaine étape : Lancement de l\'étude de faisabilité.',
                'accepte_sous_reserve' => 'Veuillez prendre en compte les réserves pour la suite du processus.',
                'travail_supplementaire' => 'Des travaux supplémentaires sont nécessaires avant de poursuivre.',
                'refuse' => 'Le TDR n\'a pas été accepté. Veuillez consulter les commentaires.',
                default => 'Veuillez consulter les détails de l\'évaluation.',
            },
            'dpaf_supervision' => 'Suivi en cours pour ce projet.',
            'equipe_organisation' => 'Vous serez notifié(e) des prochaines étapes.',
            'evaluateur_confirmation' => 'L\'évaluation a été transmise aux parties concernées.',
            default => 'Veuillez consulter les détails pour plus d\'informations.',
        };
    }

    /**
     * Get priority level for the notification.
     */
    protected function getPriorite(): string
    {
        $resultat = $this->resultatsEvaluation['resultat_global'] ?? null;

        return match($this->typeDestinataire) {
            'redacteur_resultat' => match($resultat) {
                'travail_supplementaire', 'refuse' => 'haute',
                default => 'moyenne',
            },
            'dpaf_supervision' => 'moyenne',
            default => 'normale',
        };
    }

    /**
     * Get the label for the evaluation result.
     */
    protected function getResultatLabel(): string
    {
        $resultat = $this->resultatsEvaluation['resultat_global'] ?? 'inconnu';

        return match($resultat) {
            'accepte' => 'Accepté',
            'accepte_sous_reserve' => 'Accepté sous réserve',
            'travail_supplementaire' => 'Travail supplémentaire requis',
            'refuse' => 'Refusé',
            default => ucfirst(str_replace('_', ' ', $resultat)),
        };
    }
}
