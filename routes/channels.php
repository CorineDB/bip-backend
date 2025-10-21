<?php

use App\Broadcasting\IdeeProjetChannel;
use App\Broadcasting\MinistereChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('idee.de.projet.creer.{idee}', IdeeProjetChannel::class);
Broadcast::channel('ministere.{ministere}', MinistereChannel::class);

// Canal pour les commentaires d'une ressource
Broadcast::channel('commentaires.{type}.{id}', function ($user, $type, $id) {
    info('🔍 Canal auth test', ['user' => $user, 'type' => $type, 'id' => $id]);
    // Autoriser tous les utilisateurs authentifiés à écouter les commentaires
    return $user !== null;
});

