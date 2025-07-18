<?php

namespace Database\Seeders;

use App\Models\Departement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartementSeeder extends Seeder
{
    public function run(): void
    {
        // Supprime toutes les lignes de la table
        Departement::truncate();

        $departements = [
            ['code' => 'AL', 'nom' => 'Alibori', 'slug' => 'alibori'],
            ['code' => 'AK', 'nom' => 'Atacora', 'slug' => 'atacora'],
            ['code' => 'AT', 'nom' => 'Atlantique', 'slug' => 'atlantique'],
            ['code' => 'BO', 'nom' => 'Borgou', 'slug' => 'borgou'],
            ['code' => 'CO', 'nom' => 'Collines', 'slug' => 'collines'],
            ['code' => 'KO', 'nom' => 'Kouffo', 'slug' => 'kouffo'],
            ['code' => 'DO', 'nom' => 'Donga', 'slug' => 'donga'],
            ['code' => 'LI', 'nom' => 'Littoral', 'slug' => 'littoral'],
            ['code' => 'MO', 'nom' => 'Mono', 'slug' => 'mono'],
            ['code' => 'OU', 'nom' => 'Ouémé', 'slug' => 'oueme'],
            ['code' => 'PL', 'nom' => 'Plateau', 'slug' => 'plateau'],
            ['code' => 'ZO', 'nom' => 'Zou', 'slug' => 'zou'],
        ];

        foreach ($departements as $dept) {
            DB::table('departements')->insert([
                'code' => $dept['code'],
                'nom' => $dept['nom'],
                'slug' => $dept['slug'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}