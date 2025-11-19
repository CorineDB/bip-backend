<?php

namespace App\Notifications;

use App\Models\NoteConceptuelle;
use App\Models\Projet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NoteConceptuelleSoumiseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected NoteConceptuelle $noteConceptuelle;
    protected Projet $projet;
    protected string $typeDestinataire;

    /**
     * Types de destinataires possibles :
     * - confirmation : Rédacteur de la note (confirmation de soumission)
     * - evaluation_requise : DGPD (évaluation nécessaire)
     * - information : DPAF et Chef de projet (information)
     */
    public function __construct(
        NoteConceptuelle $noteConceptuelle,
        Projet $projet,
        string $typeDestinataire = 'information'
    ) {
        $this->noteConceptuelle = $noteConceptuelle;
        $this->projet = $projet;
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
        $path = env("CLIENT_APP_URL") ?? config("app.url");

        $mailMessage = new MailMessage();

        switch ($this->typeDestinataire) {
            case 'confirmation':
                return $mailMessage
                    ->subject('Note Conceptuelle Soumise avec Succès')
                    ->greeting('Bonjour ' .$notifiable->personne->prenom . ',')
                    ->line('Votre note conceptuelle a été soumise avec succès.')
                    ->line('**Projet :** ' . $this->projet->titre_projet)
                    ->line('**Note :** ' . $this->noteConceptuelle->intitule)
                    ->line('Elle sera évaluée par la DGPD dans les prochains jours.')
                    ->action('Voir la note', url($path.'/projet/' . $this->projet->hashed_id . '/detail-note-conceptuelle'))// . $this->noteConceptuelle->hashed_id))
                    ->line('Merci pour votre soumission.');

            case 'evaluation_requise':
                return $mailMessage
                    ->subject('Nouvelle Note Conceptuelle à Évaluer')
                    ->greeting('Bonjour ' . $notifiable->personne->prenom . ',')
                    ->line('Une nouvelle note conceptuelle nécessite votre évaluation.')
                    ->line('**Projet :** ' . $this->projet->titre_projet)
                    ->line('**Ministère :** ' . ($this->projet->ministere->nom ?? 'N/A'))
                    ->line('**Soumise par :** ' . ($this->noteConceptuelle->redacteur->personne->nom ?? 'N/A') . ' ' . ($this->noteConceptuelle->redacteur->personne->prenom ?? ''))

                    ->action('Voir la note', url($path.'/projet/' . $this->projet->hashed_id . '/detail-note-conceptuelle'))
                    ->action('Évaluer maintenant', url($path.'/projet/' . $this->projet->hashed_id . '/resultat-evaluation-note-conceptuelle' . $this->noteConceptuelle->hashed_id))
                    ->line('Veuillez procéder à l\'évaluation dans les meilleurs délais.');

            case 'information':
            default:
                return $mailMessage
                    ->subject('Note Conceptuelle Soumise')
                    ->greeting('Bonjour ' .$notifiable->personne->prenom . ',')
                    ->line('Une note conceptuelle a été soumise pour évaluation.')
                    ->line('**Projet :** ' . $this->projet->titre_projet)
                    ->line('**Ministère :** ' . ($this->projet->ministere->nom ?? 'N/A'))
                    ->line('**Soumise par :** ' . ($this->noteConceptuelle->redacteur->personne->nom ?? 'N/A'))
                    ->action('Voir les détails', url($path.'/dashboard/projet/' . $this->projet->hashed_id))
                    ->line('Notification d\'information.');
        }
    }

    /**
     * Get the array representation of the notification (Database).
     */
    public function toArray($notifiable): array
    {
        return [
            'type' => 'note_conceptuelle_soumise',
            'type_destinataire' => $this->typeDestinataire,
            'note_conceptuelle_id' => $this->noteConceptuelle->hashed_id,
            'projet_id' => $this->projet->hashed_id,
            'projet_titre' => $this->projet->titre_projet,
            'note_intitule' => $this->noteConceptuelle->intitule,
            'redacteur_nom' => $this->noteConceptuelle->redacteur->personne->nom ?? '',
            'redacteur_prenom' => $this->noteConceptuelle->redacteur->personne->prenom ?? '',
            'ministere_nom' => $this->projet->ministere->nom ?? '',
            'message' => $this->getMessage(),
            'action_url' => $this->getActionUrl(),
            'action_text' => $this->getActionText(),
            'date_soumission' => $this->noteConceptuelle->created_at->format('d/m/Y H:i'),
            'priorite' => $this->getPriorite(),
        ];
    }

    /**
     * Get the broadcastable representation of the notification.
     */
    public function toBroadcast($notifiable): BroadcastMessage
    {
        return new BroadcastMessage([
            'type' => 'note_conceptuelle_soumise',
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
        switch ($this->typeDestinataire) {
            case 'confirmation':
                return 'Votre note conceptuelle "' . $this->noteConceptuelle->intitule . '" a été soumise avec succès.';

            case 'evaluation_requise':
                return 'Une nouvelle note conceptuelle "' . $this->noteConceptuelle->intitule . '" nécessite votre évaluation.';

            case 'information':
            default:
                return 'Une note conceptuelle a été soumise pour le projet "' . $this->projet->titre_projet . '".';
        }
    }

    /**
     * Get action URL based on recipient type.
     */
    protected function getActionUrl(): string
    {
        $path = env("CLIENT_APP_URL") ?? config("app.url");
        switch ($this->typeDestinataire) {
            case 'evaluation_requise':
                return $path.'/projet/' . $this->projet->hashed_id . '/resultat-evaluation-note-conceptuelle' . $this->noteConceptuelle->hashed_id;
                //'/evaluations/notes/' . $this->noteConceptuelle->hashed_id;

            case 'confirmation':
                return $path.'/projet/' . $this->projet->hashed_id . '/detail-note-conceptuelle';
                //'/projets/' . $this->projet->hashed_id . '/notes/' . $this->noteConceptuelle->hashed_id;

            case 'information':
            default:
                return $path.'/dashboard/projet/' . $this->projet->hashed_id; //'/projets/' . $this->projet->hashed_id;
        }
    }

    /**
     * Get action text based on recipient type.
     */
    protected function getActionText(): string
    {
        switch ($this->typeDestinataire) {
            case 'evaluation_requise':
                return 'Évaluer maintenant';

            case 'confirmation':
                return 'Voir la note';

            case 'information':
            default:
                return 'Voir le projet';
        }
    }

    /**
     * Get priority level for the notification.
     */
    protected function getPriorite(): string
    {
        return match($this->typeDestinataire) {
            'evaluation_requise' => 'haute',
            'confirmation' => 'moyenne',
            default => 'normale',
        };
    }
}
