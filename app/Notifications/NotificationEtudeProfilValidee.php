<?php

namespace App\Notifications;

use App\Models\Evaluation;
use App\Models\NoteConceptuelle;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationEtudeProfilValidee extends Notification implements ShouldQueue
{
    use Queueable;

    protected NoteConceptuelle $noteConceptuelle;
    protected Projet $projet;
    protected Evaluation $evaluation;
    protected User $validateur;
    protected string $decision;
    protected string $typeDestinataire;

    /**
     * Types de destinataires possibles :
     * - redacteur_resultat : Rédacteur (résultat de la validation)
     * - equipe_organisation : Équipe de l'organisation
     * - dpaf_supervision : DPAF (supervision)
     * - validateur_confirmation : Validateur DGPD (confirmation)
     * - charge_etudes_action : Chargé d'études (si étude de faisabilité préliminaire nécessaire)
     */
    public function __construct(
        NoteConceptuelle $noteConceptuelle,
        Projet $projet,
        Evaluation $evaluation,
        User $validateur,
        string $decision,
        string $typeDestinataire
    ) {
        $this->noteConceptuelle = $noteConceptuelle;
        $this->projet = $projet;
        $this->evaluation = $evaluation;
        $this->validateur = $validateur;
        $this->decision = $decision;
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
            ->line('**Décision :** ' . $this->getDecisionLabel())
            ->when($this->evaluation->commentaire, function ($mail) {
                return $mail->line('**Commentaire :** ' . $this->evaluation->commentaire);
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
            'type' => 'etude_profil_validee',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'note_conceptuelle_id' => $this->noteConceptuelle->id,
            'projet_id' => $this->projet->id,
            'evaluation_id' => $this->evaluation->id,
            'validateur_id' => $this->validateur->id,
            'validateur_name' => $this->validateur->name,
            'decision' => $this->decision,
            'decision_label' => $this->getDecisionLabel(),
            'commentaire' => $this->evaluation->commentaire,
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'action_message' => $this->getActionMessage(),
            'priorite' => $this->getPriorite(),
            'metadata' => [
                'projet_titre' => $this->projet->titre_projet,
                'note_conceptuelle_titre' => $this->noteConceptuelle->titre ?? $this->projet->titre_projet,
                'date_validation' => now()->toDateTimeString(),
            ],
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'etude_profil_validee',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'note_conceptuelle_id' => $this->noteConceptuelle->id,
            'projet_id' => $this->projet->id,
            'evaluation_id' => $this->evaluation->id,
            'validateur_id' => $this->validateur->id,
            'validateur_name' => $this->validateur->name,
            'decision' => $this->decision,
            'decision_label' => $this->getDecisionLabel(),
            'commentaire' => $this->evaluation->commentaire,
            'type_destinataire' => $this->typeDestinataire,
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'priorite' => $this->getPriorite(),
            'date_validation' => now()->toDateTimeString(),
        ]);
    }

    /**
     * Get subject based on recipient type and decision.
     */
    protected function getSubject(): string
    {
        return match($this->typeDestinataire) {
            'redacteur_resultat' => match($this->decision) {
                'faire_etude_faisabilite_preliminaire' => 'Étude de profil validée - Étude de faisabilité préliminaire requise',
                'rejeter' => 'Étude de profil rejetée',
                'ameliorer' => 'Étude de profil à améliorer',
                'transformer_en_projet' => 'Étude de profil validée - Transformation en projet mature',
                default => 'Résultat de validation de l\'étude de profil',
            },
            'charge_etudes_action' => 'Nouvelle mission : Étude de faisabilité préliminaire',
            'equipe_organisation' => 'Mise à jour : Validation de l\'étude de profil',
            'dpaf_supervision' => 'Supervision : Étude de profil validée',
            'validateur_confirmation' => 'Confirmation : Validation de l\'étude de profil effectuée',
            default => 'Étude de profil validée',
        };
    }

    /**
     * Get message based on recipient type and decision.
     */
    protected function getMessage(): string
    {
        return match($this->typeDestinataire) {
            'redacteur_resultat' => match($this->decision) {
                'faire_etude_faisabilite_preliminaire' =>
                    'Votre étude de profil pour le projet "' . $this->projet->titre_projet .
                    '" a été validée par ' . $this->validateur->name .
                    '. Une étude de faisabilité préliminaire est nécessaire avant de poursuivre.',
                'rejeter' =>
                    'Votre étude de profil pour le projet "' . $this->projet->titre_projet .
                    '" a été rejetée par ' . $this->validateur->name . '.',
                'ameliorer' =>
                    'Votre étude de profil pour le projet "' . $this->projet->titre_projet .
                    '" nécessite des améliorations selon ' . $this->validateur->name . '.',
                'transformer_en_projet' =>
                    'Félicitations ! Votre étude de profil pour le projet "' . $this->projet->titre_projet .
                    '" a été validée par ' . $this->validateur->name .
                    ' et peut être transformée directement en projet mature.',
                default =>
                    'Votre étude de profil a été évaluée par ' . $this->validateur->name . '.',
            },
            'charge_etudes_action' =>
                'Une nouvelle mission vous a été attribuée. L\'étude de profil du projet "' .
                $this->projet->titre_projet . '" a été validée et nécessite une étude de faisabilité préliminaire.',
            'equipe_organisation' =>
                'L\'étude de profil du projet "' . $this->projet->titre_projet .
                '" a été validée par ' . $this->validateur->name . '. Décision : ' . $this->getDecisionLabel() . '.',
            'dpaf_supervision' =>
                'L\'étude de profil du projet "' . $this->projet->titre_projet .
                '" a été validée. Décision : ' . $this->getDecisionLabel() . '.',
            'validateur_confirmation' =>
                'Votre validation de l\'étude de profil pour le projet "' . $this->projet->titre_projet .
                '" a été enregistrée avec succès.',
            default =>
                'L\'étude de profil a été validée pour le projet "' . $this->projet->titre_projet . '".',
        };
    }

    /**
     * Get action URL based on recipient type.
     */
    protected function getActionUrl(): string
    {
        return match($this->typeDestinataire) {
            'charge_etudes_action' => '/projets/' . $this->projet->id . '/faisabilite-preliminaire',
            'validateur_confirmation' => '/projets/' . $this->projet->id . '/evaluations',
            default => '/projets/' . $this->projet->id,
        };
    }

    /**
     * Get action text based on recipient type.
     */
    protected function getActionText(): string
    {
        return match($this->typeDestinataire) {
            'charge_etudes_action' => 'Commencer l\'étude',
            'validateur_confirmation' => 'Voir la validation',
            default => 'Voir le projet',
        };
    }

    /**
     * Get the label for the decision.
     */
    protected function getDecisionLabel(): string
    {
        return match($this->decision) {
            'faire_etude_faisabilite_preliminaire' => 'Faire une étude de faisabilité préliminaire',
            'rejeter' => 'Projet rejeté',
            'ameliorer' => 'À améliorer',
            'transformer_en_projet' => 'Transformer en projet mature',
            default => ucfirst(str_replace('_', ' ', $this->decision)),
        };
    }

    /**
     * Get action message based on decision.
     */
    protected function getActionMessage(): string
    {
        return match($this->decision) {
            'faire_etude_faisabilite_preliminaire' =>
                'Prochaine étape : Préparer le rapport de faisabilité préliminaire.',
            'rejeter' =>
                'Le projet a été rejeté et ne poursuivra pas.',
            'ameliorer' =>
                'Des améliorations sont nécessaires avant de poursuivre.',
            'transformer_en_projet' =>
                'Le projet peut passer directement en phase de maturité.',
            default =>
                'Veuillez consulter les détails pour les prochaines étapes.',
        };
    }

    /**
     * Get priority level for the notification.
     */
    protected function getPriorite(): string
    {
        return match($this->typeDestinataire) {
            'redacteur_resultat', 'charge_etudes_action' => 'haute',
            'equipe_organisation' => 'moyenne',
            default => 'normale',
        };
    }
}
