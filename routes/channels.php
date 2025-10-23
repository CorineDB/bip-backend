<?php

use App\Broadcasting\IdeeProjetChannel;
use App\Broadcasting\MinistereChannel;
use App\Models\NoteConceptuelle;
use App\Models\Projet;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    // Accepter soit l'ID numérique soit le hashed_id
    if (is_numeric($id)) {
        return (int) $user->id === (int) $id;
    }

    // Vérifier avec le hashed_id
    return isset($user->hashed_id) && $user->hashed_id === $id;
});

// Canal privé pour les notifications utilisateur
Broadcast::channel('users.{userId}', function ($user, $userId) {
    return (string) $user->id === (string) $userId;
});

// Ou si vous utilisez un hash pour l'ID
Broadcast::channel('users.{userHash}', function ($user, $userHash) {
    // Vérifier que le hash correspond à l'utilisateur
    return hash('sha256', $user->id) === $userHash;
});

Broadcast::channel('idee.de.projet.creer.{idee}', IdeeProjetChannel::class);
Broadcast::channel('ministere.{ministere}', MinistereChannel::class);

// Canal pour les commentaires d'une ressource
Broadcast::channel('commentaires.{type}.{id}', function ($user, $type, $id) {
    info('🔍 Canal auth test', ['user' => $user, 'type' => $type, 'id' => $id]);
    // Autoriser tous les utilisateurs authentifiés à écouter les commentaires
    return $user !== null;
});

/**
 * Canal privé pour un projet spécifique.
 * Permet à tous les membres liés au projet de recevoir les événements.
 */
Broadcast::channel('projets.{id}', function ($user, $id) {
    $projet = Projet::find($id);
    if (! $projet) {
        return false;
    }

    info('🔍 Canal auth test', $projet->hashed_id . " Intitule : " . $projet->titre_projet);

    // Autoriser les administrateurs ou responsables du ministère
    return $projet->ideeProjet->responsable->id == $user->id || in_array($user->role, ['dpaf', 'dgpd']) || in_array($user->type, ['dpaf', 'dgpd']);
});

/**
 * Canal privé pour un projet spécifique.
 * Permet à tous les membres liés au projet de recevoir les événements.
 */
Broadcast::channel('notes-conceptuelles.{id}', function ($user, $id) {
    $noteConceptuelle = NoteConceptuelle::find($id);
    if (! $noteConceptuelle) {
        return false;
    }

    info('🔍 Canal auth test', $noteConceptuelle->projet->hashed_id . " Intitule : " . $noteConceptuelle->projet->titre_projet);

    // Autoriser les administrateurs ou responsables du ministère
    return $noteConceptuelle->redacteur_id == $user->id || $noteConceptuelle->projet->ideeProjet->responsable->id == $user->id || in_array($user->role, ['dpaf', 'dgpd']) || in_array($user->type, ['dpaf', 'dgpd']);
});

