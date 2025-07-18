<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UpdateUsersWithCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Récupérer les IDs des nouveaux rôles
        $roles = DB::table('roles')->get()->keyBy('slug');
        $personnes = DB::table('personnes')->get()->keyBy('id');
        $organisations = DB::table('organisations')->get()->keyBy('id');

        // Supprimer les anciens utilisateurs et créer les nouveaux
        DB::table('users')->truncate();

        $users = [
            // Super Administrateur
            [
                'provider' => 'local',
                'provider_user_id' => 'super_admin_001',
                'username' => 'superadmin',
                'email' => 'superadmin@gdiz.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('SuperAdmin123!'),
                'personneId' => 1,
                'roleId' => $roles['super_admin']->id,
                'last_connection' => now()->subHours(2),
                'ip_address' => '127.0.0.1'
            ],

            // Ministre du Plan
            [
                'provider' => 'local',
                'provider_user_id' => 'ministre_plan_001',
                'username' => 'mukendi.jeanpierre',
                'email' => 'mukendi.jeanpierre@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('MinistrePlan123!'),
                'personneId' => 2,
                'roleId' => $roles['responsable_hierarchique_ministere']->id,
                'last_connection' => now()->subHours(1),
                'ip_address' => '192.168.1.101'
            ],

            // Directeur de la Planification
            [
                'provider' => 'local',
                'provider_user_id' => 'dir_plan_001',
                'username' => 'tshimanga.patrick',
                'email' => 'tshimanga.patrick@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('DirecteurPlan123!'),
                'personneId' => 3,
                'roleId' => $roles['dpaf']->id,
                'last_connection' => now()->subHours(2),
                'ip_address' => '192.168.1.102'
            ],

            // Assistant (Super Admin secondaire)
            [
                'provider' => 'local',
                'provider_user_id' => 'assistant_001',
                'username' => 'michel.claudine',
                'email' => 'michel.claudine@gdiz.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Assistant123!'),
                'personneId' => 4,
                'roleId' => $roles['super_admin']->id,
                'last_connection' => now()->subHours(3),
                'ip_address' => '192.168.1.103'
            ],

            // Conseiller (Analyste DGPD)
            [
                'provider' => 'local',
                'provider_user_id' => 'conseiller_001',
                'username' => 'rousseau.helene',
                'email' => 'rousseau.helene@dgpd.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Conseiller123!'),
                'personneId' => 5,
                'roleId' => $roles['analyste_dgpd']->id,
                'last_connection' => now()->subHours(4),
                'ip_address' => '192.168.1.104'
            ],

            // Chargé de Programme (Responsable Projet)
            [
                'provider' => 'local',
                'provider_user_id' => 'charge_prog_001',
                'username' => 'desousa.marcelle',
                'email' => 'desousa.marcelle@dpaf.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('ChargeProgr123!'),
                'personneId' => 6,
                'roleId' => $roles['responsable_projet_dpaf']->id,
                'last_connection' => now()->subHours(1),
                'ip_address' => '192.168.1.105'
            ],

            // Chargé de Programme (Cellule Technique)
            [
                'provider' => 'local',
                'provider_user_id' => 'charge_prog_002',
                'username' => 'bonnin.maggie',
                'email' => 'bonnin.maggie@dpaf.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('ChargeProgr123!'),
                'personneId' => 7,
                'roleId' => $roles['dpaf_cellule_technique']->id,
                'last_connection' => now()->subHours(2),
                'ip_address' => '192.168.1.106'
            ],

            // Consultant (Comité Validation)
            [
                'provider' => 'local',
                'provider_user_id' => 'consultant_001',
                'username' => 'renard.rene',
                'email' => 'renard.rene@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Consultant123!'),
                'personneId' => 8,
                'roleId' => $roles['comite_validation_ministeriel']->id,
                'last_connection' => now()->subHours(5),
                'ip_address' => '192.168.1.107'
            ],

            // Responsable (DGPD)
            [
                'provider' => 'local',
                'provider_user_id' => 'responsable_001',
                'username' => 'arnaud.raymond',
                'email' => 'arnaud.raymond@dgpd.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Responsable123!'),
                'personneId' => 9,
                'roleId' => $roles['dgpd']->id,
                'last_connection' => now()->subHours(3),
                'ip_address' => '192.168.1.108'
            ],

            // Chargé de Programme (Responsable Projet)
            [
                'provider' => 'local',
                'provider_user_id' => 'charge_prog_003',
                'username' => 'briand.aurelie',
                'email' => 'briand.aurelie@dpaf.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('ChargeProgr123!'),
                'personneId' => 10,
                'roleId' => $roles['responsable_projet_dpaf']->id,
                'last_connection' => now()->subHours(1),
                'ip_address' => '192.168.1.109'
            ],

            // Spécialiste (Analyste DGPD)
            [
                'provider' => 'local',
                'provider_user_id' => 'specialiste_001',
                'username' => 'mace.michel',
                'email' => 'mace.michel@dgpd.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Specialiste123!'),
                'personneId' => 11,
                'roleId' => $roles['analyste_dgpd']->id,
                'last_connection' => now()->subHours(4),
                'ip_address' => '192.168.1.110'
            ],

            // Assistant (Cellule Technique)
            [
                'provider' => 'local',
                'provider_user_id' => 'assistant_002',
                'username' => 'lefebvre.josephine',
                'email' => 'lefebvre.josephine@dpaf.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Assistant123!'),
                'personneId' => 12,
                'roleId' => $roles['dpaf_cellule_technique']->id,
                'last_connection' => now()->subHours(6),
                'ip_address' => '192.168.1.111'
            ],

            // Chargé de Programme (Responsable Projet)
            [
                'provider' => 'local',
                'provider_user_id' => 'charge_prog_004',
                'username' => 'gomes.stephanie',
                'email' => 'gomes.stephanie@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('ChargeProgr123!'),
                'personneId' => 13,
                'roleId' => $roles['responsable_projet_dpaf']->id,
                'last_connection' => now()->subHours(2),
                'ip_address' => '192.168.1.112'
            ],

            // Expert (Analyste DGPD)
            [
                'provider' => 'local',
                'provider_user_id' => 'expert_001',
                'username' => 'pottier.yves',
                'email' => 'pottier.yves@dgpd.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Expert123!'),
                'personneId' => 14,
                'roleId' => $roles['analyste_dgpd']->id,
                'last_connection' => now()->subHours(5),
                'ip_address' => '192.168.1.113'
            ],

            // Chef de Service (DPAF)
            [
                'provider' => 'local',
                'provider_user_id' => 'chef_service_001',
                'username' => 'jacques.marguerite',
                'email' => 'jacques.marguerite@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('ChefService123!'),
                'personneId' => 15,
                'roleId' => $roles['dpaf']->id,
                'last_connection' => now()->subHours(3),
                'ip_address' => '192.168.1.114'
            ],

            // Conseiller (Comité Validation)
            [
                'provider' => 'local',
                'provider_user_id' => 'conseiller_002',
                'username' => 'faivre.frederique',
                'email' => 'faivre.frederique@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Conseiller123!'),
                'personneId' => 16,
                'roleId' => $roles['comite_validation_ministeriel']->id,
                'last_connection' => now()->subHours(4),
                'ip_address' => '192.168.1.115'
            ],

            // Consultant (Comité Validation)
            [
                'provider' => 'local',
                'provider_user_id' => 'consultant_002',
                'username' => 'remy.denis',
                'email' => 'remy.denis@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Consultant123!'),
                'personneId' => 17,
                'roleId' => $roles['comite_validation_ministeriel']->id,
                'last_connection' => now()->subHours(6),
                'ip_address' => '192.168.1.116'
            ],

            // Chargé de Programme (Cellule Technique)
            [
                'provider' => 'local',
                'provider_user_id' => 'charge_prog_005',
                'username' => 'brunel.simone',
                'email' => 'brunel.simone@dpaf.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('ChargeProgr123!'),
                'personneId' => 18,
                'roleId' => $roles['dpaf_cellule_technique']->id,
                'last_connection' => now()->subHours(2),
                'ip_address' => '192.168.1.117'
            ],

            // Spécialiste (DGPD)
            [
                'provider' => 'local',
                'provider_user_id' => 'specialiste_002',
                'username' => 'boucher.odette',
                'email' => 'boucher.odette@dgpd.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Specialiste123!'),
                'personneId' => 19,
                'roleId' => $roles['dgpd']->id,
                'last_connection' => now()->subHours(1),
                'ip_address' => '192.168.1.118'
            ],

            // Gestionnaire (Analyste DGPD)
            [
                'provider' => 'local',
                'provider_user_id' => 'gestionnaire_001',
                'username' => 'marques.zacharie',
                'email' => 'marques.zacharie@dgpd.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Gestionnaire123!'),
                'personneId' => 20,
                'roleId' => $roles['analyste_dgpd']->id,
                'last_connection' => now()->subHours(3),
                'ip_address' => '192.168.1.119'
            ],

            // Gestionnaire (Responsable Projet)
            [
                'provider' => 'local',
                'provider_user_id' => 'gestionnaire_002',
                'username' => 'charpentier.luc',
                'email' => 'charpentier.luc@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Gestionnaire123!'),
                'personneId' => 21,
                'roleId' => $roles['responsable_projet_dpaf']->id,
                'last_connection' => now()->subHours(2),
                'ip_address' => '192.168.1.120'
            ],

            // Conseiller (Comité Validation)
            [
                'provider' => 'local',
                'provider_user_id' => 'conseiller_003',
                'username' => 'bousquet.jacqueline',
                'email' => 'bousquet.jacqueline@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Conseiller123!'),
                'personneId' => 22,
                'roleId' => $roles['comite_validation_ministeriel']->id,
                'last_connection' => now()->subHours(4),
                'ip_address' => '192.168.1.121'
            ],

            // Gestionnaire (DPAF)
            [
                'provider' => 'local',
                'provider_user_id' => 'gestionnaire_003',
                'username' => 'legrand.alexandria',
                'email' => 'legrand.alexandria@plan.gov.cd',
                'status' => 'actif',
                'is_email_verified' => true,
                'email_verified_at' => now(),
                'password' => Hash::make('Gestionnaire123!'),
                'personneId' => 23,
                'roleId' => $roles['dpaf']->id,
                'last_connection' => now()->subHours(5),
                'ip_address' => '192.168.1.122'
            ]
        ];

        foreach ($users as $user) {
            DB::table('users')->insert([
                'provider' => $user['provider'],
                'provider_user_id' => $user['provider_user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'status' => $user['status'],
                'is_email_verified' => $user['is_email_verified'],
                'email_verified_at' => $user['email_verified_at'],
                'password' => $user['password'],
                'personneId' => $user['personneId'],
                'roleId' => $user['roleId'],
                'last_connection' => $user['last_connection'],
                'ip_address' => $user['ip_address'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}