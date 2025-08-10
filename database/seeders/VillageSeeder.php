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
        DB::table('villages')->truncate();

        // Charger les données depuis le fichier JSON
        $jsonPath = base_path('decoupage_territorial_benin.json');
        
        if (!file_exists($jsonPath)) {
            throw new \Exception("Fichier de découpage territorial introuvable : $jsonPath");
        }

        $data = json_decode(file_get_contents($jsonPath), true);
        
        if (!$data) {
            throw new \Exception("Erreur lors de la lecture du fichier JSON");
        }

        foreach ($data as $departement) {
            foreach ($departement['communes'] as $commune) {
                foreach ($commune['arrondissements'] as $arrondissement) {
                    // Trouver l'arrondissement correspondant en base
                    $arrondissementDb = DB::table('arrondissements')
                        ->join('communes', 'arrondissements.communeId', '=', 'communes.id')
                        ->join('departements', 'communes.departementId', '=', 'departements.id')
                        ->where('arrondissements.nom', 'like', '%' . trim($arrondissement['lib_arrond']) . '%')
                        ->where('communes.nom', 'like', '%' . trim($commune['lib_com']) . '%')
                        ->where('departements.nom', 'like', '%' . trim($departement['lib_dep']) . '%')
                        ->select('arrondissements.*')
                        ->first();

                    if ($arrondissementDb && isset($arrondissement['quartiers'])) {
                        foreach ($arrondissement['quartiers'] as $index => $quartier) {
                            // Code composé : arrondissement + village
                            $codeCompose = $arrondissementDb->code . '-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT);
                            
                            // Slug unique basé sur le nom du village et l'arrondissement
                            $slugBase = Str::slug($quartier['lib_quart']);
                            $slugUnique = $slugBase . '-' . $arrondissementDb->slug;

                            DB::table('villages')->insert([
                                'code' => $codeCompose,
                                'nom' => trim($quartier['lib_quart']),
                                'slug' => $slugUnique,
                                'arrondissementId' => $arrondissementDb->id,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }
                    }
                }
            }
        }
    }
}