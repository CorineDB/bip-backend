<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganisationsSeeder extends Seeder
{
    public function run(): void
    {
        $organisations = [
            // Ministères
            [
                'nom' => 'Ministère du Plan',
                'slug' => 'ministere-plan',
                'description' => 'Ministère en charge de la planification nationale',
                'type' => 'ministere',
                'parentId' => null
            ],
            [
                'nom' => 'Ministère des Finances',
                'slug' => 'ministere-finances',
                'description' => 'Ministère des finances publiques',
                'type' => 'ministere',
                'parentId' => null
            ],

            // DPAF des ministères
            [
                'nom' => 'DPAF - Ministère du Plan',
                'slug' => 'dpaf-ministere-plan',
                'description' => 'Direction de la Planification et Administration Financière - Ministère du Plan',
                'type' => 'dpaf',
                'parentId' => 2 // Ministère du Plan
            ],

            // DGPD et DGB
            [
                'nom' => 'Direction Générale de la Planification et du Développement (DGPD)',
                'slug' => 'dgpd',
                'description' => 'Direction générale de la planification et du développement',
                'type' => 'dgpd',
                'parentId' => 2 // Ministère du Plan
            ],
            [
                'nom' => 'Direction Générale du Budget (DGB)',
                'slug' => 'dgb',
                'description' => 'Direction générale du budget',
                'type' => 'dgb',
                'parentId' => 2 // Ministère des Finances
            ]
        ];

        foreach ($organisations as $organisation) {
            DB::table('organisations')->updateOrInsert(
                ['slug' => $organisation['slug']],
                [
                    'nom' => $organisation['nom'],
                    'slug' => $organisation['slug'],
                    'description' => $organisation['description'],
                    'type' => $organisation['type'],
                    'parentId' => $organisation['parentId'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}