<?php

namespace App\Observers;

use App\Jobs\CreateDefaultOrganisationRoles;
use App\Models\Organisation;
use App\Models\Role;

class OrganisationObserver
{
    /**
     * Handle the Organisation "created" event.
     */
    public function created(Organisation $organisation): void
    {
        // Dispatcher le job pour créer les rôles par défaut uniquement pour les ministères
        if ($organisation->type === 'ministere') {
            CreateDefaultOrganisationRoles::dispatch($organisation);
        }
    }
    /**
     * Handle the Organisation "saved" event.
     */
    public function saved(Organisation $organisation): void
    {
        //
    }

    /**
     * Handle the Organisation "updated" event.
     */
    public function updated(Organisation $organisation): void
    {
        //
    }

    /**
     * Handle the Organisation "deleted" event.
     */
    public function deleted(Organisation $organisation): void
    {
        //
    }

    /**
     * Handle the Organisation "restored" event.
     */
    public function restored(Organisation $organisation): void
    {
        //
    }

    /**
     * Handle the Organisation "force deleted" event.
     */
    public function forceDeleted(Organisation $organisation): void
    {
        //
    }
}
