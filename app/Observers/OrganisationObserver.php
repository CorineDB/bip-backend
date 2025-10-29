<?php

namespace App\Observers;

use App\Jobs\CreateDefaultOrganisationRoles;
use App\Models\Organisation;
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
        // Créer les rôles par défaut pour les ministères
        if ($organisation->type === 'ministere' && $organisation->wasRecentlyCreated) {
            Log::info("OrganisationObserver::saved() déclenché", [
                'organisation_id' => $organisation->id,
                'nom' => $organisation->nom,
                'type' => $organisation->type,
                'wasRecentlyCreated' => $organisation->wasRecentlyCreated
            ]);
            CreateDefaultOrganisationRoles::dispatch($organisation);
        }
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
