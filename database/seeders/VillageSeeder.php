<?php

namespace Database\Seeders;

use App\Models\Village;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VillageSeeder extends Seeder
{
    public function run(): void

    {
        // Supprime toutes les lignes de la table
        Village::truncate();

        $villages = [
            // Villages de Godomey
            ['code' => 'GOD-001', 'nom' => 'Godomey Centre', 'slug' => 'godomey-centre', 'arrondissement_code' => 'ABM-GOD'],
            ['code' => 'GOD-002', 'nom' => 'Sédjè-Dénou', 'slug' => 'sedje-denou', 'arrondissement_code' => 'ABM-GOD'],
            ['code' => 'GOD-003', 'nom' => 'Alinakou', 'slug' => 'alinakou', 'arrondissement_code' => 'ABM-GOD'],
            ['code' => 'GOD-004', 'nom' => 'Golo-Djigbé', 'slug' => 'golo-djigbe', 'arrondissement_code' => 'ABM-GOD'],
            ['code' => 'GOD-005', 'nom' => 'Womey', 'slug' => 'womey', 'arrondissement_code' => 'ABM-GOD'],

            // Villages d'Akassato
            ['code' => 'AKA-001', 'nom' => 'Akassato Centre', 'slug' => 'akassato-centre', 'arrondissement_code' => 'ABM-AKA'],
            ['code' => 'AKA-002', 'nom' => 'Zinvié', 'slug' => 'zinvie', 'arrondissement_code' => 'ABM-AKA'],
            ['code' => 'AKA-003', 'nom' => 'Tankpè', 'slug' => 'tankpe', 'arrondissement_code' => 'ABM-AKA'],
            ['code' => 'AKA-004', 'nom' => 'Cococodji', 'slug' => 'cococodji', 'arrondissement_code' => 'ABM-AKA'],
        ];

        foreach ($villages as $village) {
            $arrondissement = DB::table('arrondissements')->where('code', $village['arrondissement_code'])->first();

            if ($arrondissement) {
                DB::table('villages')->insert([
                    'code' => $village['code'],
                    'nom' => $village['nom'],
                    'slug' => $village['slug'],
                    'arrondissementId' => $arrondissement->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}