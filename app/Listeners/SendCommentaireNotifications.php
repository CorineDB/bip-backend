<?php

namespace App\Listeners;

use App\Events\CommentaireCreated;
use App\Notifications\CommentaireCreatedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Exception;

class SendCommentaireNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CommentaireCreated $event): void
    {
        try {
            $commentaire = $event->commentaire;
            $utilisateursANotifier = collect();
            $auteurId = $commentaire->commentateurId;

            // 1. Notifier le propriétaire de la ressource commentée (si applicable)
            $ressource = $commentaire->commentaireable;
            if ($ressource) {
                // Vérifier si la ressource a un propriétaire/créateur
                $proprietaire = null;

                // Essayer différentes relations communes pour trouver le propriétaire
                if (method_exists($ressource, 'user') && $ressource->user) {
                    $proprietaire = $ressource->user;
                } elseif (isset($ressource->user_id)) {
                    $proprietaire = \App\Models\User::find($ressource->user_id);
                } elseif (method_exists($ressource, 'createur') && $ressource->createur) {
                    $proprietaire = $ressource->createur;
                } elseif (isset($ressource->created_by)) {
                    $proprietaire = \App\Models\User::find($ressource->created_by);
                }

                // Ajouter le propriétaire s'il existe et qu'il n'est pas l'auteur du commentaire
                if ($proprietaire && $proprietaire->id !== $auteurId) {
                    $utilisateursANotifier->push($proprietaire);
                }
            }

            // 2. Notifier les autres utilisateurs qui ont commenté la même ressource
            $autresCommentateurs = \App\Models\Commentaire::where('commentaireable_type', $commentaire->commentaireable_type)
                ->where('commentaireable_id', $commentaire->commentaireable_id)
                ->where('commentateurId', '!=', $auteurId)
                ->where('id', '!=', $commentaire->id)
                ->with('commentateur')
                ->get()
                ->pluck('commentateur')
                ->filter()
                ->unique('id');

            foreach ($autresCommentateurs as $commentateur) {
                $utilisateursANotifier->push($commentateur);
            }

            // 3. Si c'est une réponse, notifier l'auteur du commentaire parent
            if ($commentaire->commentaire_id && $commentaire->parent) {
                $auteurParent = $commentaire->parent->commentateur;
                if ($auteurParent && $auteurParent->id !== $auteurId) {
                    $utilisateursANotifier->push($auteurParent);
                }
            }

            // Supprimer les doublons et envoyer les notifications
            $utilisateursUniques = $utilisateursANotifier->unique('id');

            foreach ($utilisateursUniques as $utilisateur) {
                try {
                    $utilisateur->notify(new CommentaireCreatedNotification($commentaire));
                } catch (Exception $e) {
                    Log::warning('Erreur lors de l\'envoi de la notification à un utilisateur', [
                        'commentaire_id' => $commentaire->id,
                        'utilisateur_id' => $utilisateur->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('Notifications envoyées pour le commentaire via listener', [
                'commentaire_id' => $commentaire->id,
                'nombre_utilisateurs' => $utilisateursUniques->count()
            ]);

        } catch (Exception $e) {
            // Ne pas faire échouer le job si les notifications échouent
            Log::error('Erreur dans le listener SendCommentaireNotifications', [
                'commentaire_id' => $event->commentaire->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }
}
