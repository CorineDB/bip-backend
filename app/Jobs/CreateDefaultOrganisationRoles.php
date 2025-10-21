<?php

namespace App\Jobs;

use App\Models\Organisation;
use App\Models\Role;
use App\Models\Permission;
use App\Repositories\DpafRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CreateDefaultOrganisationRoles implements ShouldQueue
{
    use Queueable;

    protected Organisation $organisation;

    /**
     * Create a new job instance.
     */
    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::beginTransaction();

        try {
            // Définir les rôles de base à créer
            $defaultRoles = $this->getDefaultRoles();

            foreach ($defaultRoles as $roleData) {
                // Vérifier si le rôle existe déjà pour cette organisation
                $existingRole = Role::where('slug', $roleData['slug'])
                    ->where('roleable_type', Organisation::class)
                    ->where('roleable_id', $this->organisation->id)
                    ->first();

                if ($existingRole) {
                    Log::info("Role {$roleData['slug']} already exists for organisation {$this->organisation->id}");
                    continue;
                }

                // Créer le rôle pour cette organisation
                $role = Role::create([
                    'nom' => $roleData['nom'],
                    'slug' => $roleData['slug'],
                    'description' => $roleData['description'],
                    'roleable_id' => $this->organisation->id,
                    'roleable_type' => Organisation::class,
                ]);

                // Attacher les permissions au rôle
                if (!empty($roleData['permissions'])) {
                    $permissions = Permission::whereIn('slug', $roleData['permissions'])->pluck('id')->toArray();

                    if (!empty($permissions)) {
                        $role->permissions()->attach($permissions);
                    }
                }

                Log::info("Role {$roleData['slug']} created successfully for organisation {$this->organisation->id}");
            }

            $attributs = [
                "id_ministere" => $this->organisation->id,
                "nom" => "Direction de la Planification, de l'Administration et des Finances (DPAF)",
                "description" => "Direction administrative présente du {$this->organisation->nom}, chargée de la gestion des ressources humaines, financières et matérielles, ainsi que des services généraux au sein du ministère."
            ];

            app(DpafRepository::class)->getModel()->firstOrCreate(['id_ministere' => $attributs['id_ministere']], $attributs);


            DB::commit();

            Log::info("Default roles created successfully for organisation {$this->organisation->nom} (ID: {$this->organisation->id})");
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Failed to create default roles for organisation {$this->organisation->id}: " . $e->getMessage(), [
                'organisation_id' => $this->organisation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Définir les rôles de base et leurs permissions
     */
    protected function getDefaultRoles(): array
    {
        return [
            [
                'nom' => 'DPAF',
                'slug' => 'dpaf',
                'description' => 'Directeur de la Planification, de l\'Administration et des Finances',
                'permissions' => [
                    'creer-une-idee-de-projet',
                    'modifier-une-idee-de-projet',
                    'supprimer-une-idee-de-projet',
                    'voir-les-idees-de-projet',
                    'soumettre-une-idee-de-projet',
                    'creer-une-note-conceptuelle',
                    'modifier-une-note-conceptuelle',
                    'rediger-une-note-conceptuelle',
                    'voir-les-notes-conceptuelles',
                    'valider-une-note-conceptuelle',
                    'gerer-les-utilisateurs',
                    'gerer-les-roles',
                    'gerer-les-permissions',
                ]
            ],
            [
                'nom' => 'Responsable de Projet',
                'slug' => 'responsable-projet',
                'description' => 'Responsable de la gestion et du suivi des projets',
                'permissions' => [
                    'creer-une-idee-de-projet',
                    'modifier-une-idee-de-projet',
                    'voir-les-idees-de-projet',
                    'soumettre-une-idee-de-projet',
                    'creer-une-note-conceptuelle',
                    'modifier-une-note-conceptuelle',
                    'rediger-une-note-conceptuelle',
                    'voir-les-notes-conceptuelles',
                    'voir-les-projets',
                    'modifier-un-projet',
                ]
            ],
            [
                'nom' => 'Chargé d\'Études',
                'slug' => 'charge-etudes',
                'description' => 'Chargé de la réalisation des études de projet',
                'permissions' => [
                    'voir-les-idees-de-projet',
                    'voir-les-notes-conceptuelles',
                    'rediger-une-note-conceptuelle',
                    'voir-les-projets',
                ]
            ],
        ];
    }
}
