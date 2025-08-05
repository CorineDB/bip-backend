<?php

use App\Broadcasting\IdeeProjetChannel;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
/*
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('idee.de.projet.creer.{idee}', IdeeProjetChannel::class);
Broadcast::channel('auto.evalation.climatique.preliminiaire.{idee}', IdeeProjetChannel::class);*/
