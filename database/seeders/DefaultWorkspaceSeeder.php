<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Personne;
use App\Models\Organisation;
use Illuminate\Support\Facades\DB;

class DefaultWorkspaceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Création des espaces de travail par défaut...');


        /**
         * Creer l'instance DPAF
         */
        // Supprime toutes les lignes de la table
        DB::table('dpaf')->truncate();

        /**
         * Creer le compte admin de la DPAF
         *
         * lui attribuer le role dpaf
         *
         * utilise la fonction create de DpafService
         *
         * active le compte utilisateur automatiquement
         */


        /**
         * Creer les roles de la DPAF
         * Responsable projet (DPAF)
         */

        /**
         * Creer l'organisation de type ministere
         */

        /**
         * Creer un compte utilisateur de la dpaf (profilable_type == App\\Models\\Dpaf) mais du ministere sectoriel (organisation)
         * avec le role Responsable projet
         */

        $this->command->info('✅ Espaces de travail créés avec succès !');
    }

    /**
     * Créer les permissions pour l'évaluation climatique
     */
    private function createEvaluationPermissions(): void
    {
        $this->command->info('📋 Création des permissions d\'évaluation...');

        $permissions = [
            // Gestion des évaluations
            [
                'nom' => 'Créer une évaluation',
                'slug' => 'evaluation.create',
                'description' => 'Peut créer une nouvelle évaluation climatique'
            ],
            [
                'nom' => 'Voir les évaluations',
                'slug' => 'evaluation.view',
                'description' => 'Peut consulter les évaluations'
            ],
            [
                'nom' => 'Modifier une évaluation',
                'slug' => 'evaluation.edit',
                'description' => 'Peut modifier les paramètres d\'une évaluation'
            ],
            [
                'nom' => 'Supprimer une évaluation',
                'slug' => 'evaluation.delete',
                'description' => 'Peut supprimer une évaluation'
            ],
            [
                'nom' => 'Finaliser une évaluation',
                'slug' => 'evaluation.finalize',
                'description' => 'Peut finaliser et valider une évaluation'
            ],

            // Gestion des évaluateurs
            [
                'nom' => 'Assigner des évaluateurs',
                'slug' => 'evaluation.assign-evaluators',
                'description' => 'Peut assigner des évaluateurs à une évaluation'
            ],
            [
                'nom' => 'Évaluer des critères',
                'slug' => 'evaluation.evaluate-criteria',
                'description' => 'Peut noter et évaluer des critères'
            ],
            [
                'nom' => 'Voir le progrès des évaluations',
                'slug' => 'evaluation.view-progress',
                'description' => 'Peut consulter le progrès des évaluations'
            ],

            // Gestion des critères
            [
                'nom' => 'Gérer les critères',
                'slug' => 'criteria.manage',
                'description' => 'Peut créer, modifier, supprimer des critères'
            ],
            [
                'nom' => 'Voir les critères',
                'slug' => 'criteria.view',
                'description' => 'Peut consulter les critères d\'évaluation'
            ],

            // Gestion des projets
            [
                'nom' => 'Créer des idées de projet',
                'slug' => 'project-idea.create',
                'description' => 'Peut créer des idées de projet'
            ],
            [
                'nom' => 'Modifier des idées de projet',
                'slug' => 'project-idea.edit',
                'description' => 'Peut modifier des idées de projet'
            ],
            [
                'nom' => 'Voir les idées de projet',
                'slug' => 'project-idea.view',
                'description' => 'Peut consulter les idées de projet'
            ],

            // Administration
            [
                'nom' => 'Gérer les utilisateurs',
                'slug' => 'users.manage',
                'description' => 'Peut créer, modifier, supprimer des utilisateurs'
            ],
            [
                'nom' => 'Gérer les rôles',
                'slug' => 'roles.manage',
                'description' => 'Peut créer, modifier, supprimer des rôles'
            ],
        ];

        foreach ($permissions as $permissionData) {
            Permission::firstOrCreate(
                ['slug' => $permissionData['slug']],
                $permissionData
            );
        }

        $this->command->info('✅ ' . count($permissions) . ' permissions créées');
    }

    /**
     * Créer les rôles par défaut
     */
    private function createDefaultRoles(): array
    {
        $this->command->info('👥 Création des rôles par défaut...');

        $rolesData = [
            [
                'nom' => 'Super Administrateur',
                'slug' => 'super-admin',
                'description' => 'Accès complet à toutes les fonctionnalités',
                'permissions' => '*' // Toutes les permissions
            ],
            [
                'nom' => 'Administrateur d\'Évaluation',
                'slug' => 'evaluation-admin',
                'description' => 'Peut gérer les évaluations et assigner les évaluateurs',
                'permissions' => [
                    'evaluation.create', 'evaluation.view', 'evaluation.edit', 'evaluation.delete',
                    'evaluation.finalize', 'evaluation.assign-evaluators', 'evaluation.view-progress',
                    'criteria.view', 'project-idea.view'
                ]
            ],
            [
                'nom' => 'Évaluateur Expert',
                'slug' => 'evaluator-expert',
                'description' => 'Expert qui peut évaluer des critères climatiques',
                'permissions' => [
                    'evaluation.view', 'evaluation.evaluate-criteria', 'evaluation.view-progress',
                    'criteria.view', 'project-idea.view'
                ]
            ],
            [
                'nom' => 'Évaluateur Standard',
                'slug' => 'evaluator-standard',
                'description' => 'Évaluateur avec accès limité',
                'permissions' => [
                    'evaluation.evaluate-criteria', 'criteria.view', 'project-idea.view'
                ]
            ],
            [
                'nom' => 'Gestionnaire de Projet',
                'slug' => 'project-manager',
                'description' => 'Peut gérer les idées de projet',
                'permissions' => [
                    'project-idea.create', 'project-idea.edit', 'project-idea.view',
                    'evaluation.view'
                ]
            ],
            [
                'nom' => 'Consultant',
                'slug' => 'consultant',
                'description' => 'Accès en lecture seule',
                'permissions' => [
                    'evaluation.view', 'criteria.view', 'project-idea.view'
                ]
            ]
        ];

        $roles = [];
        $allPermissions = Permission::all();

        foreach ($rolesData as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'nom' => $roleData['nom'],
                    'slug' => $roleData['slug'],
                    'description' => $roleData['description']
                ]
            );

            // Assigner les permissions
            if ($roleData['permissions'] === '*') {
                // Super admin a toutes les permissions
                $role->permissions()->sync($allPermissions->pluck('id'));
            } else {
                // Assigner les permissions spécifiques
                $permissionIds = $allPermissions
                    ->whereIn('slug', $roleData['permissions'])
                    ->pluck('id');
                $role->permissions()->sync($permissionIds);
            }

            $roles[$roleData['slug']] = $role;
        }

        $this->command->info('✅ ' . count($roles) . ' rôles créés avec permissions');
        return $roles;
    }

    /**
     * Créer l'organisation par défaut
     */
    private function createDefaultOrganisation(): Organisation
    {
        $this->command->info('🏢 Création de l\'organisation par défaut...');

        return Organisation::firstOrCreate(
            ['nom' => 'GDIZ - Direction Générale'],
            [
                'nom' => 'GDIZ - Direction Générale',
                'sigle' => 'GDIZ-DG',
                'description' => 'Organisation par défaut pour l\'évaluation climatique des projets',
                'adresse' => 'Cameroun',
                'telephone' => '+237 000 000 000',
                'email' => 'admin@gdiz.org',
                'type' => 'gouvernementale'
            ]
        );
    }

    /**
     * Créer les utilisateurs par défaut
     */
    private function createDefaultUsers(array $roles, Organisation $organisation): void
    {
        $this->command->info('👤 Création des utilisateurs par défaut...');

        $usersData = [
            [
                'username' => 'superadmin',
                'email' => 'superadmin@gdiz.org',
                'role' => 'super-admin',
                'personne' => [
                    'nom' => 'Administrateur',
                    'prenom' => 'Super',
                    'fonction' => 'Administrateur Système'
                ]
            ],
            [
                'username' => 'admin.evaluation',
                'email' => 'admin.evaluation@gdiz.org',
                'role' => 'evaluation-admin',
                'person' => [
                    'nom' => 'Kouam',
                    'prenom' => 'Marie',
                    'fonction' => 'Responsable Évaluations Climatiques'
                ]
            ],
            [
                'username' => 'expert.climat',
                'email' => 'expert.climat@gdiz.org',
                'role' => 'evaluator-expert',
                'person' => [
                    'nom' => 'Ngono',
                    'prenom' => 'Paul',
                    'fonction' => 'Expert Climatique Senior'
                ]
            ],
            [
                'username' => 'expert.environnement',
                'email' => 'expert.environnement@gdiz.org',
                'role' => 'evaluator-expert',
                'person' => [
                    'nom' => 'Fouda',
                    'prenom' => 'Claire',
                    'fonction' => 'Experte Environnementale'
                ]
            ],
            [
                'username' => 'evaluateur1',
                'email' => 'evaluateur1@gdiz.org',
                'role' => 'evaluator-standard',
                'person' => [
                    'nom' => 'Mbida',
                    'prenom' => 'Jean',
                    'fonction' => 'Évaluateur'
                ]
            ],
            [
                'username' => 'chef.projet',
                'email' => 'chef.projet@gdiz.org',
                'role' => 'project-manager',
                'person' => [
                    'nom' => 'Bello',
                    'prenom' => 'Aminata',
                    'fonction' => 'Chef de Projet'
                ]
            ],
            [
                'username' => 'consultant',
                'email' => 'consultant@gdiz.org',
                'role' => 'consultant',
                'person' => [
                    'nom' => 'Consultant',
                    'prenom' => 'External',
                    'fonction' => 'Consultant Externe'
                ]
            ]
        ];

        foreach ($usersData as $userData) {
            // Créer la personne
            $personne = Personne::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'nom' => $userData['person']['nom'],
                    'prenom' => $userData['person']['prenom'],
                    'email' => $userData['email'],
                    'telephone' => '+237 000 000 000',
                    'fonction' => $userData['person']['fonction'],
                    'organismeId' => $organisation->id
                ]
            );

            // Créer l'utilisateur
            $user = User::firstOrCreate(
                ['email' => $userData['email']],
                [
                    'username' => $userData['username'],
                    'email' => $userData['email'],
                    'password' => Hash::make('password123'), // Mot de passe par défaut
                    'is_email_verified' => true,
                    'email_verified_at' => now(),
                    'status' => 'active',
                    'personneId' => $personne->id,
                    'roleId' => $roles[$userData['role']]->id,
                    'provider' => 'local',
                    'person' => [
                        'nom' => $userData['person']['nom'],
                        'prenom' => $userData['person']['prenom'],
                        'fonction' => $userData['person']['fonction']
                    ]
                ]
            );

            $this->command->info("✅ Utilisateur créé: {$userData['username']} ({$userData['role']})");
        }

        $this->command->info('🔑 Mot de passe par défaut pour tous les utilisateurs: password123');
    }
}