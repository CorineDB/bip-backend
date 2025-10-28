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
        Log::info("OrganisationObserver::saved() déclenché", [
            'organisation_id' => $organisation->id,
            'nom' => $organisation->nom,
            'type' => $organisation->type,
            'wasRecentlyCreated' => $organisation->wasRecentlyCreated
        ]);

        // Créer les rôles par défaut uniquement si le ministère vient d'être créé
        if ($organisation->type === 'ministere' && $organisation->wasRecentlyCreated) {
            Log::info("Dispatch du job CreateDefaultOrganisationRoles pour {$organisation->nom}");
            CreateDefaultOrganisationRoles::dispatch($organisation);
        } else {
            Log::info("Job CreateDefaultOrganisationRoles NON dispatché", [
                'raison' => !($organisation->type === 'ministere') ? 'Type n\'est pas ministere' : 'Organisation n\'est pas wasRecentlyCreated'
            ]);
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
