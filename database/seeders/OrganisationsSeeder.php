<?php

namespace Database\Seeders;

use App\Models\Organisation;
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
                ]
            );
        }
    }
}