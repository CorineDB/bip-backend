<?php

namespace App\Observers;

use App\Jobs\CreateDefaultOrganisationRoles;
use App\Models\Organisation;
use App\Models\Role;
use Illuminate\Support\Facades\Log;

class OrganisationObserver
{
    /**
     * Handle the Organisation "created" event.
     */
    public function created(Organisation $organisation): void
    {
        //
    }

    /**
     * Handle the Organisation "saved" event.
     *
     * Déclenché après create() et update()
     */
    public function saved(Organisation $organisation): void
    {
        // Créer les rôles par défaut uniquement si le ministère vient d'être créé
        if ($organisation->type === 'ministere' && $organisation->wasRecentlyCreated) {
            CreateDefaultOrganisationRoles::dispatch($organisation);
        }
    }

    /**
     * Handle the Organisation "updated" event.
     */
    public function updated(Organisation $organisation): void
    {
        // Créer les rôles par défaut uniquement si le ministère vient d'être créé
        if ($organisation->type === 'ministere' && $organisation->wasRecentlyCreated) {
            CreateDefaultOrganisationRoles::dispatch($organisation);
        }
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
