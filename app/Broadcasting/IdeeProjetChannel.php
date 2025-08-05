<?php

namespace App\Broadcasting;

use App\Models\IdeeProjet;
use App\Models\User;

class IdeeProjetChannel
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
     */
    public function join(User $user, IdeeProjet $ideeProjet): array|bool
    {
        return $user->id === $ideeProjet->responsable->id;
    }
}
