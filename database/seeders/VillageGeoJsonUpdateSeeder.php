<?php

namespace Database\Seeders;

use App\Helpers\SlugHelper;
use App\Models\Village;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VillageGeoJsonUpdateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        /*$json = file_get_contents(public_path('decoupage_territorial_benin.json'));
        $data = json_decode($json, true);

        $arrondissements = collect($data)
            ->pluck('communes')
            ->flatten(1)
            ->pluck('arrondissements')
            ->flatten(1);

        DB::table("villages")->truncate();

        // Générer tous les arrondissements basés sur les données du CommuneSeeder
        foreach ($arrondissements as $arrondissement) {
            // Récupérer l'ID de la commune
            $arrondissementRecord = DB::table('arrondissements')->where('slug', Str::slug($arrondissement['lib_arrond']))->first();

            if ($arrondissementRecord && isset($arrondissement['quartiers'])) {
                foreach ($arrondissement['quartiers'] as $index => $quartier) {

                    $code = $arrondissementRecord->code . '-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT);
                    //$slug = Str::slug($quartier["lib_quart"]);
                    $slug = Str::slug(SlugHelper::generateUnique($quartier["lib_quart"], Village::class));


                    Village::updateOrCreate([
                        'code' => $code,
                        'slug' => $slug,
                        'arrondissementId' => $arrondissementRecord->id
                    ], [

                        'code' => $code,
                        'nom' => Str::title($quartier["lib_quart"]),
                        'slug' => $slug,
                        'arrondissementId' => $arrondissementRecord->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $this->command->info('Mise à jour (Upsert) des villages par GeoJSON terminée. Aucun village existant n\'a été supprimé. Count : ' . Village::count());*/


        // 1. CHARGEMENT et PRÉPARATION des données GeoJSON
        $filePath = public_path('geodata/data_chef_lieu_village.geojson');

        if (!file_exists($filePath)) {
            $this->command->error('Le fichier GeoJSON est introuvable à : ' . $filePath);
            return;
        }

        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        $features = $data['features'] ?? [];

        // --- Fonction de normalisation pour la cohérence ---
        // Utilisons une fonction anonyme pour simuler votre SlugHelper::rmAccents()
        // NOTE: Si votre helper est disponible globalement ou dans un namespace, utilisez-le directement.
        $normalize = function ($text) {
            // C'est la fonction que vous avez fournie.
            return Str::slug(SlugHelper::rmAccents($text));
            // Si SlugHelper n'est pas disponible, utilisez : return Str::slug($text);
            // Si le slug est trop restrictif et que vous voulez juste retirer les accents pour la comparaison:
            // return iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        };

        // 2. Charger TOUS les arrondissements existants dans une Map (Optimisation)
        // La clé doit être le SLUG du nom de l'Arrondissement pour une recherche rapide.
        $arrondissementsMap = DB::table('arrondissements')
            ->get(['id', 'code', 'nom', 'slug'])
            ->keyBy(fn($item) => $normalize($item->nom));
        // La clé ici est le nom normalisé (sans accents/caractères spéciaux)

        // 2. Charger TOUS les villages existants dans une Map (Optimisation)
        // La clé doit être le SLUG du nom de l'Arrondissement pour une recherche rapide.
        $villagesMap = DB::table('villages')
            ->get(['id', 'code', 'slug', 'nom', 'arrondissementId'])
            ->keyBy(fn($item) => $normalize($item->nom));
        $villagesMap = DB::table('villages')
            ->get(['id', 'code', 'slug', 'nom', 'arrondissementId'])
            ->keyBy(fn($item) => $item->arrondissementId . '-' . $normalize($item->nom));

        // La clé ici est le nom normalisé (sans accents/caractères spéciaux)

        // Compteur de villages par arrondissement pour générer des codes séquentiels
        $villageCountByArrond = DB::table('villages')
            ->select('arrondissementId', DB::raw('COUNT(*) as count'))
            ->groupBy('arrondissementId')
            ->pluck('count', 'arrondissementId')
            ->toArray();

        $codesToKeep = [];
        $villagesData = []; // Tableau pour stocker les nouveaux villages à créer

        // 3. PARSING DU GEOJSON et PRÉPARATION du tableau d'upsert
        foreach ($features as $index => $feature) {
            $properties = $feature['properties'];

            // Récupération des données du GeoJSON
            $arrondName = $properties['Arrondisst'] ?? null;
            $villageName = $properties['Nom_LOC'] ?? $properties['Village_Ad'] ?? null;
            $codeGeo = $properties['Code_GEO'] ?? null;
            $latitude = $properties['Latitude'] ?? null;
            $longitude = $properties['Longitude'] ?? null;

            if (!$arrondName || !$villageName || !$codeGeo || !$latitude || !$longitude) {
                continue; // Ignore les enregistrements GeoJSON incomplets
            }

            $arrondNormalizedKey = $normalize($arrondName);
            $arrondRecord = $arrondissementsMap->get($arrondNormalizedKey);

            if ($arrondRecord) {
                $villageNormalizedKey = $arrondRecord->id . '-' . $normalize($villageName);
                $villageRecord = $villagesMap->get($villageNormalizedKey);

                if ($villageRecord) {/*
                    $villageRecord->update([
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]); */
                    $village = Village::find($villageRecord->id);

                    $this->command->info("Before Mise à jour (Upsert) des villages par GeoJSON terminée. Village : $index {$village->id} {$village->nom} {$village->slug} {$village->latitude} {$village->longitude}");


                    $this->command->info("Log Lat $index {$latitude} {$longitude}");

                    $village->update([
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ]);

                    $this->command->info("AFter Mise à jour (Upsert) des villages par GeoJSON terminée. Village : $index {$village->id} {$village->nom} {$village->slug} {$village->latitude} {$village->longitude}");

                }
                else{
                    // Incrémenter le compteur pour cet arrondissement
                    if (!isset($villageCountByArrond[$arrondRecord->id])) {
                        $villageCountByArrond[$arrondRecord->id] = 0;
                    }
                    $villageCountByArrond[$arrondRecord->id]++;

                    // Générer un code séquentiel basé sur le nombre de villages dans cet arrondissement
                    $code = $arrondRecord->code . '-' . str_pad($villageCountByArrond[$arrondRecord->id], 2, '0', STR_PAD_LEFT);
                    $slug = Str::slug(SlugHelper::generateUnique($villageName, Village::class));

                    $villagesData[] = [
                        'code' => $code,
                        'slug' => $slug,
                        'nom' => Str::title($villageName),
                        'arrondissementId' => $arrondRecord->id,
                        'longitude' => $longitude,
                        'latitude' => $latitude,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    $this->command->info("➕ Nouveau village à créer : {$villageName} (Code: {$code})");
                }
            }
        }

        // 4. INSERTION des nouveaux villages en batch
        if (!empty($villagesData)) {
            DB::table('villages')->insert($villagesData);
            $this->command->info('✅ ' . count($villagesData) . ' nouveaux villages créés à partir du GeoJSON.');
        } else {
            $this->command->info('ℹ️ Aucun nouveau village à créer.');
        }

        $this->command->info('✅ Mise à jour terminée. Total villages : ' . Village::count());

        /*{
        // 1. CHARGEMENT et PRÉPARATION des données GeoJSON
        $filePath = public_path('geodata/data_chef_lieu_village.geojson');

        if (!file_exists($filePath)) {
            $this->command->error('Le fichier GeoJSON est introuvable à : ' . $filePath);
            return;
        }

        $json = file_get_contents($filePath);
        $data = json_decode($json, true);

        $features = $data['features'] ?? [];

        $villagesToUpsert = [];

        // --- Fonction de normalisation pour la cohérence ---
        // Utilisons une fonction anonyme pour simuler votre SlugHelper::rmAccents()
        // NOTE: Si votre helper est disponible globalement ou dans un namespace, utilisez-le directement.
        $normalize = function ($text) {
            // C'est la fonction que vous avez fournie.
            return Str::slug(SlugHelper::rmAccents($text));
            // Si SlugHelper n'est pas disponible, utilisez : return Str::slug($text);
            // Si le slug est trop restrictif et que vous voulez juste retirer les accents pour la comparaison:
            // return iconv('UTF-8', 'ASCII//TRANSLIT', $text);
        };

        // 2. Charger TOUS les arrondissements existants dans une Map (Optimisation)
        // La clé doit être le SLUG du nom de l'Arrondissement pour une recherche rapide.
        $arrondissementsMap = DB::table('arrondissements')
            ->get(['id', 'code', 'nom'])
            ->keyBy(fn($item) => $normalize($item->nom));
            // La clé ici est le nom normalisé (sans accents/caractères spéciaux)

        $codesToKeep = [];

        // 3. PARSING DU GEOJSON et PRÉPARATION du tableau d'upsert
        foreach ($features as $index => $feature) {
            $properties = $feature['properties'];

            // Récupération des données du GeoJSON
            $arrondName = $properties['Arrondisst'] ?? null;
            $villageName = $properties['Village_Ad'] ?? $properties['Nom_LOC'] ?? null;
            $codeGeo = $properties['Code_GEO'] ?? null;
            $latitude = $properties['Latitude'] ?? null;
            $longitude = $properties['Longitude'] ?? null;

            if (!$arrondName || !$villageName || !$codeGeo || !$latitude || !$longitude) {
                continue; // Ignore les enregistrements GeoJSON incomplets
            }

            $arrondNormalizedKey = $normalize($arrondName);
            $arrondRecord = $arrondissementsMap->get($arrondNormalizedKey);

            if ($arrondRecord) {
                // B. Génération du Slug du Village (Normalisation pour le champ 'slug')
                // On retire les accents AVANT de générer le slug pour une cohérence maximale
                $villageNameWithoutAccents = SlugHelper::rmAccents($villageName);
                $slug = Str::slug($villageNameWithoutAccents);

                $villagesToUpsert[] = [
                    // CLÉ D'IDENTIFICATION UNIQUE (C'est la colonne que Laravel va utiliser pour chercher/mettre à jour)
                    //'code_geo' => $codeGeo,

                    // DONNÉES À METTRE À JOUR/INSÉRER
                    // NOTE: Le champ 'code' interne n'est plus généré avec l'index,
                    // car il n'est pas fiable. On utilise Code_GEO.
                    //'code' => $codeGeo, // On peut mettre Code_GEO dans 'code' si on le souhaite, ou le laisser tel quel.

                    // Autres champs mis à jour/insérés
                    'code' => $arrondRecord->code . '-' . str_pad($index + 1, 2, '0', STR_PAD_LEFT), // Optionnel: Générez un code interne si Code_GEO n'est pas utilisé pour les relations
                    'nom' => Str::title($villageName),
                    'slug' => $slug,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'arrondissementId' => $arrondRecord->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $codesToKeep[] = $codeGeo;
            }
        }

        // 4. EXÉCUTION de l'Upsert (Ajout et Mise à Jour)
        if (!empty($villagesToUpsert)) {
            DB::table('villages')->upsert(
                $villagesToUpsert,
                // Colonne(s) utilisée(s) pour identifier les doublons :
                // La combinaison du slug et de l'Arrondissement ID est la clé naturelle.
                ['arrondissementId', 'code', 'slug'],
                // Colonnes à mettre à jour si un doublon est trouvé
                ['nom', 'slug', 'latitude', 'longitude', 'arrondissementId', 'updated_at']
            );
        }

        // 5. OMISSION DE LA SUPPRESSION
        // Les villages déjà présents en base mais absents du GeoJSON sont conservés.

        $this->command->info('Mise à jour (Upsert) des villages par GeoJSON terminée. Aucun village existant n\'a été supprimé.');
        */
    }
}
