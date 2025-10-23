<?php

namespace App\Notifications;

use App\Models\Evaluation;
use App\Models\NoteConceptuelle;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class AppreciationNoteConceptuelleNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Evaluation $evaluation;
    protected NoteConceptuelle $noteConceptuelle;
    protected Projet $projet;
    protected User $evaluateur;
    protected string $typeDestinataire;

    /**
     * Types de destinataires possibles :
     * - redacteur_info : Rédacteur de la note (information)
     * - dpaf_supervision : DPAF du ministère (supervision)
     * - dgpd_collegial : Autres membres DGPD (information collégiale)
     * - chef_projet_evaluation_terminee : Chef de projet (évaluation terminée)
     * - evaluateur_confirmation : Évaluateur (confirmation)
     */
    public function __construct(
        Evaluation $evaluation,
        NoteConceptuelle $noteConceptuelle,
        Projet $projet,
        User $evaluateur,
        string $typeDestinataire = 'redacteur_info'
    ) {
        $this->evaluation = $evaluation;
        $this->noteConceptuelle = $noteConceptuelle;
        $this->projet = $projet;
        $this->evaluateur = $evaluateur;
        $this->typeDestinataire = $typeDestinataire;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database', 'broadcast', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail($notifiable): MailMessage
    {
        $mailMessage = new MailMessage();
        $evaluateurNomComplet = $this->evaluateur->prenom . ' ' . $this->evaluateur->nom;

        switch ($this->typeDestinataire) {
            case 'redacteur_info':
                return $mailMessage
                    ->subject('Appréciation de votre Note Conceptuelle en cours')
                    ->greeting('Bonjour ' . $notifiable->prenom . ',')
                    ->line('Votre note conceptuelle est en cours d\'évaluation.')
                    ->line('**Projet :** ' . $this->projet->titre_projet)
                    ->line('**Note :** ' . $this->noteConceptuelle->intitule)
                    ->line('**Évaluateur :** ' . $evaluateurNomComplet)
                    ->line('**Type d\'appréciation :** ' . $this->getTypeAppreciationLabel())
                    ->action('Suivre l\'évaluation', $this->getActionUrl())
                    ->line('Vous serez notifié une fois l\'évaluation terminée.');

            case 'dpaf_supervision':
                return $mailMessage
                    ->subject('Appréciation Note Conceptuelle - Supervision DPAF')
                    ->greeting('Bonjour ' . $notifiable->prenom . ',')
                    ->line('Une appréciation de note conceptuelle est en cours pour un projet de votre ministère.')
                    ->line('**Projet :** ' . $this->projet->titre_projet)
                    ->line('**Note :** ' . $this->noteConceptuelle->intitule)
                    ->line('**Évaluateur :** ' . $evaluateurNomComplet)
                    ->line('**Rédacteur :** ' . ($this->noteConceptuelle->redacteur->nom ?? 'N/A'))
                    ->action('Voir le projet', url('/projets/' . $this->projet->id))
                    ->line('Cette notification vous est envoyée à titre de supervision.');

            case 'dgpd_collegial':
                return $mailMessage
                    ->subject('Appréciation Note Conceptuelle par un collègue')
                    ->greeting('Bonjour ' . $notifiable->prenom . ',')
                    ->line('Un collègue a commencé l\'appréciation d\'une note conceptuelle.')
                    ->line('**Projet :** ' . $this->projet->titre_projet)
                    ->line('**Note :** ' . $this->noteConceptuelle->intitule)
                    ->line('**Évaluateur :** ' . $evaluateurNomComplet)
                    ->line('**Ministère :** ' . ($this->projet->ministere->nom ?? 'N/A'))
                    ->action('Consulter l\'évaluation', $this->getActionUrl())
                    ->line('Notification d\'information collégiale.');

            case 'chef_projet_evaluation_terminee':
                $score = $this->evaluation->score ?? 'N/A';
                $decision = $this->evaluation->decision ?? 'en attente';

                return $mailMessage
                    ->subject('Évaluation de Note Conceptuelle Terminée')
                    ->greeting('Bonjour ' . $notifiable->prenom . ',')
                    ->line('L\'évaluation de votre note conceptuelle est terminée.')
                    ->line('**Projet :** ' . $this->projet->titre_projet)
                    ->line('**Note :** ' . $this->noteConceptuelle->intitule)
                    ->line('**Score :** ' . $score)
                    ->line('**Décision :** ' . ucfirst($decision))
                    ->line('**Évaluateur :** ' . $evaluateurNomComplet)
                    ->action('Voir les résultats', $this->getActionUrl())
                    ->line('Vous pouvez maintenant consulter les détails de l\'évaluation.');

            case 'evaluateur_confirmation':
                return $mailMessage
                    ->subject('Confirmation - Appréciation Note Conceptuelle créée')
                    ->greeting('Bonjour ' . $notifiable->prenom . ',')
                    ->line('Votre appréciation de note conceptuelle a été enregistrée avec succès.')
                    ->line('**Projet :** ' . $this->projet->titre_projet)
                    ->line('**Note :** ' . $this->noteConceptuelle->intitule)
                    ->line('**Type d\'appréciation :** ' . $this->getTypeAppreciationLabel())
                    ->line('**Statut :** ' . ucfirst($this->evaluation->statut ?? 'en cours'))
                    ->action('Continuer l\'évaluation', $this->getActionUrl())
                    ->line('Merci pour votre contribution à l\'évaluation des projets.');

            default:
                return $mailMessage
                    ->subject('Appréciation Note Conceptuelle')
                    ->greeting('Bonjour ' . $notifiable->prenom . ',')
                    ->line('Une appréciation de note conceptuelle a été créée.')
                    ->action('Voir les détails', $this->getActionUrl());
        }
    }

    /**
     * Get the array representation of the notification (Database).
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'appreciation_note_conceptuelle',
            'type_destinataire' => $this->typeDestinataire,
            'evaluation_id' => $this->evaluation->id,
            'note_conceptuelle_id' => $this->noteConceptuelle->id,
            'projet_id' => $this->projet->id,
            'projet_titre' => $this->projet->titre_projet,
            'note_intitule' => $this->noteConceptuelle->intitule,
            'evaluateur' => [
                'id' => $this->evaluateur->id,
                'nom' => $this->evaluateur->nom,
                'prenom' => $this->evaluateur->prenom,
                'nom_complet' => $this->evaluateur->prenom . ' ' . $this->evaluateur->nom,
            ],
            'redacteur' => [
                'nom' => $this->noteConceptuelle->redacteur->nom ?? '',
                'prenom' => $this->noteConceptuelle->redacteur->prenom ?? '',
            ],
            'ministere_nom' => $this->projet->ministere->nom ?? '',
            'type_appreciation' => $this->evaluation->type ?? 'note-conceptuelle',
            'type_appreciation_label' => $this->getTypeAppreciationLabel(),
            'statut_evaluation' => $this->evaluation->statut ?? 'en_cours',
            'score' => $this->evaluation->score ?? null,
            'decision' => $this->evaluation->decision ?? null,
            'message' => $this->getMessage(),
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'date_appreciation' => $this->evaluation->created_at->format('d/m/Y H:i'),
            'priorite' => $this->getPriorite(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'appreciation_note_conceptuelle',
            'message' => $this->getMessage(),
            'priorite' => $this->getPriorite(),
            'data' => $this->toArray($notifiable),
        ]);
    }

    /**
     * Get personalized message based on recipient type.
     */
    protected function getMessage(): string
    {
        $evaluateurNom = $this->evaluateur->prenom . ' ' . $this->evaluateur->nom;

        switch ($this->typeDestinataire) {
            case 'redacteur_info':
                return 'Votre note conceptuelle "' . $this->noteConceptuelle->intitule . '" est en cours d\'évaluation par ' . $evaluateurNom . '.';

            case 'dpaf_supervision':
                return 'Appréciation en cours pour le projet "' . $this->projet->titre_projet . '" par ' . $evaluateurNom . '.';

            case 'dgpd_collegial':
                return $evaluateurNom . ' a commencé l\'appréciation de "' . $this->noteConceptuelle->intitule . '".';

            case 'chef_projet_evaluation_terminee':
                $decision = $this->evaluation->decision ?? 'en attente';
                return 'Évaluation terminée pour "' . $this->noteConceptuelle->intitule . '". Décision : ' . ucfirst($decision) . '.';

            case 'evaluateur_confirmation':
                return 'Votre appréciation pour "' . $this->noteConceptuelle->intitule . '" a été enregistrée avec succès.';

            default:
                return 'Une appréciation de note conceptuelle a été créée pour "' . $this->projet->titre_projet . '".';
        }
    }

    /**
     * Get action URL based on recipient type.
     */
    protected function getActionUrl(): string
    {
        switch ($this->typeDestinataire) {
            case 'evaluateur_confirmation':
            case 'dgpd_collegial':
                return '/evaluations/' . $this->evaluation->id . '/edit';

            case 'chef_projet_evaluation_terminee':
            case 'redacteur_info':
                return '/projets/' . $this->projet->id . '/notes/' . $this->noteConceptuelle->id . '/evaluations';

            case 'dpaf_supervision':
            default:
                return '/projets/' . $this->projet->id;
        }
    }

    /**
     * Get action text based on recipient type.
     */
    protected function getActionText(): string
    {
        switch ($this->typeDestinataire) {
            case 'evaluateur_confirmation':
                return 'Continuer l\'évaluation';

            case 'chef_projet_evaluation_terminee':
                return 'Voir les résultats';

            case 'redacteur_info':
                return 'Suivre l\'évaluation';

            case 'dgpd_collegial':
                return 'Consulter l\'évaluation';

            case 'dpaf_supervision':
            default:
                return 'Voir le projet';
        }
    }

    /**
     * Get the label for appreciation type.
     */
    protected function getTypeAppreciationLabel(): string
    {
        $type = $this->evaluation->type ?? 'note-conceptuelle';

        return match($type) {
            'note-conceptuelle' => 'Appréciation Climatique',
            'appreciation_pertinence' => 'Appréciation de Pertinence',
            'appreciation_faisabilite' => 'Appréciation de Faisabilité',
            default => ucfirst(str_replace('_', ' ', $type)),
        };
    }

    /**
     * Get priority level for the notification.
     */
    protected function getPriorite(): string
    {
        return match($this->typeDestinataire) {
            'chef_projet_evaluation_terminee' => 'haute',
            'redacteur_info', 'evaluateur_confirmation' => 'moyenne',
            default => 'normale',
        };
    }
}
