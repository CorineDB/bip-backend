<?php

namespace Database\Seeders;

use App\Models\CategorieProjet;
use App\Models\TypeIntervention;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class DomaineCategorieSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $domaines = [
            'Infrastructures et cadre de vie' => [
                'Transport',
                'Urbanisme et habitat',
                'Eau potable et assainissement',
                'Énergie',
                'Infrastructures numériques',
            ],
            'Éducation et formation' => [
                'Éducation de base',
                'Éducation secondaire',
                'Formation professionnelle',
                'Enseignement supérieur',
            ],
            'Santé et protection sociale' => [
                'Services de santé',
                'Protection sociale',
                'Santé maternelle et infantile',
                'Lutte contre les épidémies',
            ],
            'Agriculture, élevage et pêche' => [
                'Agriculture',
                'Élevage',
                'Pêche et aquaculture',
                'Développement rural',
            ],
            'Environnement et développement durable' => [
                'Gestion des déchets',
                'Protection de la biodiversité',
                'Changement climatique',
                'Reboisement et lutte contre la désertification',
            ],
            'Économie, emploi, industrie et artisanat' => [
                'Industrie',
                'Artisanat',
                'Emploi et insertion professionnelle',
                'Développement des PME',
            ],
            'Numérique, TIC et innovation' => [
                'Infrastructures numériques',
                'Innovation technologique',
                'Digitalisation des services publics',
                'E-gouvernance',
            ],
            'Gouvernance, justice et institutions' => [
                'Justice',
                'Sécurité',
                'Lutte contre la corruption',
                'Réformes institutionnelles',
            ],
            'Tourisme, culture et patrimoine' => [
                'Tourisme',
                'Culture',
                'Patrimoine historique',
                'Développement des arts',
            ],
        ];

        foreach ($domaines as $domaineNom => $categories) {
            /*TypeIntervention::updateOrCreate(
                ['type_intervent' => $domaineNom],
                //['sectionId' => ??]
            );*/

            foreach ($categories as $categorieNom) {
                CategorieProjet::updateOrCreate(
                    ['categorie' => $categorieNom, /* 'domaine_intervention_id' => $domaine->id */],
                    ['slug' => Str::slug($categorieNom)]
                );
            }
        }
    }
}
