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
        // Charger les données depuis le fichier JSON
        $json = file_get_contents(public_path('decoupage_territorial_benin.json'));
        $data = json_decode($json, true);

        if (!$data) {
            throw new \Exception("Erreur lors de la lecture du fichier JSON");
        }

        // Extraire tous les arrondissements du JSON
        $arrondissements = collect($data)
            ->pluck('communes')
            ->flatten(1)
            ->pluck('arrondissements')
            ->flatten(1);

        // Supprimer tous les villages existants
        DB::table('villages')->truncate();

        // Générer tous les villages basés sur les arrondissements
        foreach ($arrondissements as $arrondissement) {
            // Récupérer l'arrondissement correspondant en base par slug
            $arrondissementRecord = DB::table('arrondissements')
                ->where('slug', Str::slug($arrondissement['lib_arrond']))
                ->first();

            if ($arrondissementRecord && isset($arrondissement['quartiers'])) {
                foreach ($arrondissement['quartiers'] as $index => $quartier) {
                    // Code composé : code arrondissement + numéro séquentiel
                    $code = $arrondissementRecord->code . '-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                    
                    // Slug simple du quartier/village
                    $slug = Str::slug($quartier['lib_quart']);

                    // Créer ou mettre à jour le village
                    Village::updateOrCreate([
                        'code' => $code,
                        'slug' => $slug,
                        'arrondissementId' => $arrondissementRecord->id
                    ], [
                        'code' => $code,
                        'nom' => Str::title($quartier['lib_quart']),
                        'slug' => $slug,
                        'arrondissementId' => $arrondissementRecord->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}