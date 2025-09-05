<?php

namespace App\Broadcasting;

use App\Models\User;
use App\Models\Ministere;
use App\Models\Organisation;

class MinistereChannel
{
    /**
     * Create a new channel instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     * L'utilisateur peut rejoindre le channel s'il appartient au ministère
     */
    public function join(User $user, Organisation $ministere): array|bool
    {
        // Vérifier si l'utilisateur appartient à ce ministère
        return $user->profilable->ministere->id === $ministere->whereNull("parentId")->id;
    }
}