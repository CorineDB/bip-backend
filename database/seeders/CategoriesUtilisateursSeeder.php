<?php

namespace Database\Seeders;

use App\Traits\ForeignKeyConstraints;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesUtilisateursSeeder extends Seeder
{
    use ForeignKeyConstraints;

    public function run(): void
    {
        $this->disableForeignKeyChecks();
        // Supprimer les anciens rôles et créer les nouveaux
        DB::table('roles')->truncate();

        $this->enableForeignKeyChecks();

        $roles = [
            [
                'nom' => 'Responsable Projet',
                'slug' => 'responsable_projet_dpaf',
                'description' => 'Responsable de projet au niveau DPAF ou ministère sectoriel - Peut créer des fiches idées de projet, obtenir le score climatique et soumettre des rapports de faisabilité',
                'roleable_type' => null,
                'roleable_id' => null
            ],
            [
                'nom' => 'Responsable Hiérarchique',
                'slug' => 'responsable_hierarchique_ministere',
                'description' => 'Responsable hiérarchique au niveau ministériel - Valide et soumet les fiches d\'idées de projet',
                'roleable_type' => null,
                'roleable_id' => null
            ],
            [
                'nom' => 'DPAF',
                'slug' => 'dpaf',
                'description' => 'Direction de la Planification et de l\'Administration Financière - Analyse les fiches d\'idées, rédige les notes conceptuelles, soumet les TDRs et rapports',
                'roleable_type' => null,
                'roleable_id' => null
            ],
            [
                'nom' => 'DPAF/Cellule Technique/Service Etude',
                'slug' => 'dpaf_cellule_technique',
                'description' => 'Cellule technique ou service d\'étude DPAF - Évalue les notes conceptuelles',
                'roleable_type' => null,
                'roleable_id' => null
            ],
            [
                'nom' => 'Analyste DGPD',
                'slug' => 'analyste_dgpd',
                'description' => 'Analyste de la Direction Générale de la Planification et du Développement - Applique l\'AMC, évalue les impacts climatiques, valide les étapes d\'analyse',
                'roleable_type' => null,
                'roleable_id' => null
            ],
            [
                'nom' => 'Comité de Validation Ministériel',
                'slug' => 'comite_validation_ministeriel',
                'description' => 'Comité de validation au niveau ministériel - Valide les projets aux différentes étapes (profil, préfaisabilité, faisabilité)',
                'roleable_type' => null,
                'roleable_id' => null
            ],
            [
                'nom' => 'DGPD',
                'slug' => 'dgpd',
                'description' => 'Direction Générale de la Planification et du Développement - Valide les projets, apprécie les TDRs, soumet les rapports',
                'roleable_type' => null,
                'roleable_id' => null
            ],
            [
                'nom' => 'Super Administrateur',
                'slug' => 'super_admin',
                'description' => 'Accès complet à toutes les fonctionnalités du système',
                'roleable_type' => null,
                'roleable_id' => null
            ]
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insert([
                'nom' => $role['nom'],
                'slug' => $role['slug'],
                'description' => $role['description'],
                'roleable_type' => $role['roleable_type'],
                'roleable_id' => $role['roleable_id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        $this->disableForeignKeyChecks();
        // Créer les permissions spécifiques
        DB::table('permissions')->truncate();

        $this->enableForeignKeyChecks();

        $permissions = [
            // Permissions pour Responsable Projet (DPAF/Ministère Sectoriel)
            [
                'nom' => 'Créer fiche idée de projet',
                'slug' => 'create_project_idea_form',
                'description' => 'Permet de créer une fiche d\'idée de projet'
            ],
            [
                'nom' => 'Modifier fiche idée de projet',
                'slug' => 'update_project_idea_form',
                'description' => 'Permet de modifier une fiche d\'idée de projet'
            ],
            [
                'nom' => 'Supprimer fiche idée de projet',
                'slug' => 'delete_project_idea_form',
                'description' => 'Permet de supprimer une fiche d\'idée de projet'
            ],
            [
                'nom' => 'Voir fiches idées de projet',
                'slug' => 'view_project_idea_forms',
                'description' => 'Permet de consulter les fiches d\'idées de projet'
            ],
            [
                'nom' => 'Obtenir score climatique',
                'slug' => 'obtain_climate_score',
                'description' => 'Permet d\'obtenir le score climatique d\'un projet'
            ],
            [
                'nom' => 'Transmettre score climatique',
                'slug' => 'transmit_climate_score',
                'description' => 'Permet de transmettre le score climatique'
            ],
            [
                'nom' => 'Soumettre rapport de faisabilité',
                'slug' => 'submit_feasibility_report',
                'description' => 'Permet de soumettre un rapport de faisabilité'
            ],

            // Permissions pour Responsable Hiérarchique (Ministère)
            [
                'nom' => 'Valider fiche idée de projet',
                'slug' => 'validate_project_idea_form',
                'description' => 'Permet de valider une fiche d\'idée de projet'
            ],
            [
                'nom' => 'Soumettre fiche idée de projet',
                'slug' => 'submit_project_idea_form',
                'description' => 'Permet de soumettre une fiche d\'idée de projet'
            ],

            // Permissions pour DPAF
            [
                'nom' => 'Analyser fiche idée de projet',
                'slug' => 'analyze_project_idea_form',
                'description' => 'Permet d\'analyser une fiche d\'idée de projet'
            ],
            [
                'nom' => 'Rédiger note conceptuelle',
                'slug' => 'write_conceptual_note',
                'description' => 'Permet de rédiger une note conceptuelle'
            ],
            [
                'nom' => 'Soumettre TDRs préfaisabilité',
                'slug' => 'submit_prefeasibility_tdr',
                'description' => 'Permet de soumettre les TDRs de préfaisabilité à la DGPD'
            ],
            [
                'nom' => 'Soumettre rapport préfaisabilité',
                'slug' => 'submit_prefeasibility_report',
                'description' => 'Permet de soumettre le rapport de préfaisabilité'
            ],

            // Permissions pour DPAF/Cellule Technique/Service Etude
            [
                'nom' => 'Évaluer note conceptuelle',
                'slug' => 'evaluate_conceptual_note',
                'description' => 'Permet d\'évaluer une note conceptuelle'
            ],

            // Permissions pour Analyste DGPD
            [
                'nom' => 'Appliquer analyse multicritères',
                'slug' => 'apply_multicriteria_analysis',
                'description' => 'Permet d\'appliquer l\'analyse multicritères (AMC)'
            ],
            [
                'nom' => 'Évaluer impacts climatiques',
                'slug' => 'evaluate_climate_impacts',
                'description' => 'Permet d\'appliquer l\'évaluation préliminaire des impacts climatiques'
            ],
            [
                'nom' => 'Valider étape analyse',
                'slug' => 'validate_analysis_step',
                'description' => 'Permet de valider l\'étape d\'analyse'
            ],

            // Permissions pour Comité de Validation Ministériel
            [
                'nom' => 'Valider étude de profil',
                'slug' => 'validate_profile_study',
                'description' => 'Permet de valider le projet à l\'étape Etude de profil'
            ],
            [
                'nom' => 'Valider étude de préfaisabilité',
                'slug' => 'validate_prefeasibility_study',
                'description' => 'Permet de valider le projet à l\'étape Etude de préfaisabilité'
            ],
            [
                'nom' => 'Apprécier TDRs faisabilité',
                'slug' => 'appreciate_feasibility_tdr',
                'description' => 'Permet d\'apprécier les TDRs de faisabilité'
            ],
            [
                'nom' => 'Valider étude de faisabilité',
                'slug' => 'validate_feasibility_study',
                'description' => 'Permet de valider le projet à l\'étape Etude de faisabilité'
            ],

            // Permissions pour DGPD
            [
                'nom' => 'Valider projet étude profil (DGPD)',
                'slug' => 'dgpd_validate_profile_study',
                'description' => 'Permet à la DGPD de valider le projet à l\'étape Etude de profil'
            ],
            [
                'nom' => 'Recevoir TDRs préfaisabilité',
                'slug' => 'receive_prefeasibility_tdr',
                'description' => 'Permet de recevoir les TDRs de préfaisabilité'
            ],
            [
                'nom' => 'Apprécier TDRs préfaisabilité',
                'slug' => 'appreciate_prefeasibility_tdr',
                'description' => 'Permet d\'apprécier les TDRs de préfaisabilité'
            ],
            [
                'nom' => 'Recevoir rapport préfaisabilité',
                'slug' => 'receive_prefeasibility_report',
                'description' => 'Permet de recevoir le rapport de préfaisabilité'
            ],
            [
                'nom' => 'Valider projet préfaisabilité (DGPD)',
                'slug' => 'dgpd_validate_prefeasibility_study',
                'description' => 'Permet à la DGPD de valider le projet à l\'étape Etude de préfaisabilité'
            ],
            [
                'nom' => 'Apprécier TDRs faisabilité (DGPD)',
                'slug' => 'dgpd_appreciate_feasibility_tdr',
                'description' => 'Permet à la DGPD d\'apprécier les TDRs de faisabilité'
            ],
            [
                'nom' => 'Valider projet faisabilité (DGPD)',
                'slug' => 'dgpd_validate_feasibility_study',
                'description' => 'Permet à la DGPD de valider le projet à l\'étape Etude de faisabilité'
            ],

            // Permissions système
            [
                'nom' => 'Administration système',
                'slug' => 'system_admin',
                'description' => 'Accès complet à l\'administration du système'
            ],
            [
                'nom' => 'Voir rapports',
                'slug' => 'view_reports',
                'description' => 'Permet de consulter les rapports'
            ],
            [
                'nom' => 'Exporter données',
                'slug' => 'export_data',
                'description' => 'Permet d\'exporter les données'
            ]
        ];

        foreach ($permissions as $permission) {
            DB::table('permissions')->insert([
                'nom' => $permission['nom'],
                'slug' => $permission['slug'],
                'description' => $permission['description'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }

        // Créer la table de liaison role-permission si elle n'existe pas
        $this->createRolePermissionAssociations();
    }

    private function createRolePermissionAssociations(): void
    {
        // Récupérer les IDs des rôles et permissions
        $roles = DB::table('roles')->get()->keyBy('slug');
        $permissions = DB::table('permissions')->get()->keyBy('slug');

        // Définir les associations rôle-permission
        $rolePermissions = [
            // Responsable Projet (DPAF/Ministère Sectoriel) - CRUD sur fiches idées
            'responsable_projet_dpaf' => [
                'create_project_idea_form',
                'update_project_idea_form',
                'delete_project_idea_form',
                'view_project_idea_forms',
                'obtain_climate_score',
                'transmit_climate_score',
                'submit_feasibility_report'
            ],

            // Responsable Hiérarchique (Ministère) - CRU (pas de Delete)
            'responsable_hierarchique_ministere' => [
                'view_project_idea_forms',
                'update_project_idea_form',
                'validate_project_idea_form',
                'submit_project_idea_form'
            ],

            // DPAF
            'dpaf' => [
                'analyze_project_idea_form',
                'write_conceptual_note',
                'submit_prefeasibility_tdr',
                'submit_prefeasibility_report',
                'view_project_idea_forms'
            ],

            // DPAF/Cellule Technique/Service Etude
            'dpaf_cellule_technique' => [
                'evaluate_conceptual_note',
                'view_project_idea_forms'
            ],

            // Analyste DGPD
            'analyste_dgpd' => [
                'apply_multicriteria_analysis',
                'evaluate_climate_impacts',
                'validate_analysis_step',
                'view_project_idea_forms'
            ],

            // Comité de Validation Ministériel
            'comite_validation_ministeriel' => [
                'validate_profile_study',
                'validate_prefeasibility_study',
                'appreciate_feasibility_tdr',
                'validate_feasibility_study',
                'view_project_idea_forms'
            ],

            // DGPD
            'dgpd' => [
                'dgpd_validate_profile_study',
                'receive_prefeasibility_tdr',
                'appreciate_prefeasibility_tdr',
                'receive_prefeasibility_report',
                'dgpd_validate_prefeasibility_study',
                'dgpd_appreciate_feasibility_tdr',
                'dgpd_validate_feasibility_study',
                'view_project_idea_forms'
            ],

            // Super Administrateur - Toutes les permissions
            'super_admin' => array_keys($permissions->toArray())
        ];

        // Insérer les associations dans la table role_permissions
        foreach ($rolePermissions as $roleSlug => $permissionSlugs) {
            $roleId = $roles[$roleSlug]->id;

            foreach ($permissionSlugs as $permissionSlug) {
                if (isset($permissions[$permissionSlug])) {
                    DB::table('role_permissions')->updateOrInsert(
                        [
                            'roleId' => $roleId,
                            'permissionId' => $permissions[$permissionSlug]->id
                        ],
                        [
                            'created_at' => now(),
                            'updated_at' => now()
                        ]
                    );
                }
            }
        }
    }
}