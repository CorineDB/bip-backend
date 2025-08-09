<?php

namespace App\Observers;

use App\Models\Organisation;
use App\Models\Role;

class OrganisationObserver
{
    /**
     * Handle the Organisation "created" event.
     */
    public function created(Organisation $organisation): void
    {

    }
    /**
     * Handle the Organisation "created" event.
     */
    public function saved(Organisation $organisation): void
    {
        if ($organisation->type === "ministere") {
            $roles = [
                ['slug' => 'dpaf', 'nom' => 'DPAF', 'description' => "Departement de la planification des administrations et des finances du ministere '{$organisation->nom}'."],
                ['slug' => 'responsable-projet', 'nom' => 'Responsable Projet', "description" => "Responsable des projet du ministere '{$organisation->nom}'"],
                ['slug' => 'responsable-hierarchique', 'nom' => 'Responsable Hiérarchique', 'description' => "Manager hiérarchique des responsables projets du minister '{$organisation->nom}"],
            ];

            foreach ($roles as $roleData) {
                Role::firstOrCreate([
                    'slug' => $roleData['slug'],
                    'roleable_id' => $organisation->id,
                    'roleable_type' => Organisation::class,
                ], [
                    'nom' => $roleData['nom'],
                    'slug' => $roleData['slug'],
                    'description' => $roleData['description']
                ]);
            }
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
