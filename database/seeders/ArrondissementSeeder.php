<?php

namespace Database\Seeders;

use App\Models\Arrondissement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ArrondissementSeeder extends Seeder
{
    public function run(): void
    {
        // Supprime toutes les lignes de la table
        Arrondissement::truncate();

        $arrondissements = [
            // Arrondissements d'Abomey-Calavi
            ['code' => 'ABM-AKA', 'nom' => 'Akassato', 'slug' => 'akassato', 'commune_code' => 'ABM'],
            ['code' => 'ABM-GOD', 'nom' => 'Godomey', 'slug' => 'godomey', 'commune_code' => 'ABM'],
            ['code' => 'ABM-HEV', 'nom' => 'Hêvié', 'slug' => 'hevie', 'commune_code' => 'ABM'],
            ['code' => 'ABM-KOT', 'nom' => 'Kotopa', 'slug' => 'kotopa', 'commune_code' => 'ABM'],
            ['code' => 'ABM-OKE', 'nom' => 'Oké-Odan', 'slug' => 'oke-odan', 'commune_code' => 'ABM'],
            ['code' => 'ABM-TON', 'nom' => 'Togba', 'slug' => 'togba', 'commune_code' => 'ABM'],

            // Arrondissements de Cotonou
            ['code' => 'COT-01', 'nom' => '1er Arrondissement', 'slug' => '1er-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-02', 'nom' => '2ème Arrondissement', 'slug' => '2eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-03', 'nom' => '3ème Arrondissement', 'slug' => '3eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-04', 'nom' => '4ème Arrondissement', 'slug' => '4eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-05', 'nom' => '5ème Arrondissement', 'slug' => '5eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-06', 'nom' => '6ème Arrondissement', 'slug' => '6eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-07', 'nom' => '7ème Arrondissement', 'slug' => '7eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-08', 'nom' => '8ème Arrondissement', 'slug' => '8eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-09', 'nom' => '9ème Arrondissement', 'slug' => '9eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-10', 'nom' => '10ème Arrondissement', 'slug' => '10eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-11', 'nom' => '11ème Arrondissement', 'slug' => '11eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-12', 'nom' => '12ème Arrondissement', 'slug' => '12eme-arrondissement', 'commune_code' => 'COT'],
            ['code' => 'COT-13', 'nom' => '13ème Arrondissement', 'slug' => '13eme-arrondissement', 'commune_code' => 'COT'],
        ];

        foreach ($arrondissements as $arrondissement) {
            $commune = DB::table('communes')->where('code', $arrondissement['commune_code'])->first();

            if ($commune) {
                DB::table('arrondissements')->insert([
                    'code' => $arrondissement['code'],
                    'nom' => $arrondissement['nom'],
                    'slug' => $arrondissement['slug'],
                    'communeId' => $commune->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}