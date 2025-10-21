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

class NotificationEtudeFaisabiliteValidee extends Notification implements ShouldQueue
{
    use Queueable;

    protected Rapport $rapport;
    protected Projet $projet;
    protected Evaluation $evaluation;
    protected User $validateur;
    protected string $decision;
    protected string $typeDestinataire;

    /**
     * Types de destinataires possibles :
     * - redacteur_resultat : Rédacteur/soumetteur (résultat de la validation)
     * - dpaf_supervision : DPAF du ministère (supervision)
     * - equipe_organisation : Équipe de l'organisation (information)
     * - validateur_confirmation : Validateur (confirmation)
     * - charge_mise_en_oeuvre_action : Chargé d'études (si étude de faisabilité nécessaire)
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
            ->line('**Validé par :** ' . $this->validateur->name)
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
            'type' => 'etude_faisabilite_validee',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'rapport_id' => $this->rapport->id,
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
                'rapport_intitule' => $this->rapport->intitule,
                'date_validation' => now()->toDateTimeString(),
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
            'type' => 'etude_faisabilite_validee',
            'titre' => $this->getSubject(),
            'message' => $this->getMessage(),
            'rapport_id' => $this->rapport->id,
            'projet_id' => $this->projet->id,
            'evaluation_id' => $this->evaluation->id,
            'validateur_id' => $this->validateur->id,
            'validateur_name' => $this->validateur->name,
            'decision' => $this->decision,
            'decision_label' => $this->getDecisionLabel(),
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
                'maturite' => 'Étude de faisabilité validée - Projet mature',
                'faisabilite' => 'Étude de faisabilité validée - Mise en œuvre requise',
                'reprendre' => 'Étude de faisabilité à reprendre',
                'abandonner' => 'Étude de faisabilité - Projet abandonné',
                default => 'Résultat de validation de l\'étude de faisabilité',
            },
            'charge_mise_en_oeuvre_action' => 'Nouvelle mission : Étude de faisabilité',
            'dpaf_supervision' => 'Supervision : Étude de faisabilité validée',
            'equipe_organisation' => 'Mise à jour : Validation de l\'étude de faisabilité',
            'validateur_confirmation' => 'Confirmation : Validation de l\'étude enregistrée',
            default => 'Étude de faisabilité validée',
        };
    }

    /**
     * Get message based on recipient type and decision.
     */
    protected function getMessage(): string
    {
        return match($this->typeDestinataire) {
            'redacteur_resultat' => match($this->decision) {
                'maturite' =>
                    'Félicitations ! L\'étude de faisabilité pour le projet "' . $this->projet->titre_projet .
                    '" a été validée par ' . $this->validateur->name . '. Le projet peut passer en phase de maturité.',
                'faisabilite' =>
                    'L\'étude de faisabilité pour le projet "' . $this->projet->titre_projet .
                    '" a été validée par ' . $this->validateur->name . '. Une étude de faisabilité complète est nécessaire.',
                'reprendre' =>
                    'L\'étude de faisabilité pour le projet "' . $this->projet->titre_projet .
                    '" doit être reprise selon les commentaires de ' . $this->validateur->name . '.',
                'abandonner' =>
                    'L\'étude de faisabilité pour le projet "' . $this->projet->titre_projet .
                    '" a conduit à l\'abandon du projet selon la décision de ' . $this->validateur->name . '.',
                default =>
                    'L\'étude de faisabilité a été validée par ' . $this->validateur->name . '.',
            },
            'charge_mise_en_oeuvre_action' =>
                'Une nouvelle mission vous a été attribuée. L\'étude de faisabilité du projet "' .
                $this->projet->titre_projet . '" requiert une étude de faisabilité complète.',
            'dpaf_supervision' =>
                'L\'étude de faisabilité du projet "' . $this->projet->titre_projet .
                '" a été validée. Décision : ' . $this->getDecisionLabel() . '.',
            'equipe_organisation' =>
                'L\'étude de faisabilité du projet "' . $this->projet->titre_projet .
                '" a été validée par ' . $this->validateur->name . '. Décision : ' . $this->getDecisionLabel() . '.',
            'validateur_confirmation' =>
                'Votre validation de l\'étude de faisabilité pour le projet "' . $this->projet->titre_projet .
                '" a été enregistrée avec succès.',
            default =>
                'L\'étude de faisabilité a été validée pour le projet "' . $this->projet->titre_projet . '".',
        };
    }

    /**
     * Get action URL based on recipient type.
     */
    protected function getActionUrl(): string
    {
        return match($this->typeDestinataire) {
            'charge_mise_en_oeuvre_action' => '/projets/' . $this->projet->id . '/faisabilite',
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
            'charge_mise_en_oeuvre_action' => 'Commencer l\'étude',
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
            'maturite' => 'Projet mature',
            'faisabilite' => 'Mise en œuvre requise',
            'reprendre' => 'À reprendre',
            'abandonner' => 'Projet abandonné',
            default => ucfirst(str_replace('_', ' ', $this->decision)),
        };
    }

    /**
     * Get action message based on decision.
     */
    protected function getActionMessage(): string
    {
        return match($this->decision) {
            'maturite' =>
                'Prochaine étape : Le projet peut passer en phase de maturité.',
            'faisabilite' =>
                'Prochaine étape : Lancement de l\'étude de faisabilité complète.',
            'reprendre' =>
                'Des révisions sont nécessaires avant de poursuivre.',
            'abandonner' =>
                'Le projet a été abandonné et ne poursuivra pas.',
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
            'redacteur_resultat', 'charge_mise_en_oeuvre_action' => 'haute',
            'dpaf_supervision', 'equipe_organisation' => 'moyenne',
            default => 'normale',
        };
    }
}
