<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GrilleEvaluationPreliminaireSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorieCritere = \App\Models\CategorieCritere::firstOrCreate([
            'type' => 'Évaluation préliminaire multi projet de l\'impact climatique'
        ], [
            'slug' => 'evaluation-preliminaire-multi-projet-impact-climatique',
            'is_mandatory' => true
        ]);

        // Critère Atténuation
        $critereAttenuation = \App\Models\Critere::firstOrCreate([
            'intitule' => 'Atténuation',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 3.9,
            'commentaire' => 'La réduction des émissions de gaz à effet de serre résultant de la mise en œuvre du projet, avec la taxonomie de l\'UE comme guide pour établir un seuil en cas d\'ambiguïté concernant l\'importance de la contribution d\'un projet à la réduction des émissions de GES (par exemple, un projet d\'électricité avec des émissions de cycle de vie >100 g CO2e/kWh doit être classé comme « Faible atténuation » même s\'il prétend le contraire).',
            'is_mandatory' => true
        ]);

        // Notations pour Atténuation
        $notationsAttenuation = [
            ['libelle' => 'Négatif', 'valeur' => '-3', 'commentaire' => 'Le projet aggrave les impacts climatiques en augmentant significativement les émissions de gaz à effet de serre (GES) ou en amplifiant les vulnérabilités environnementales (déforestation, destruction des écosystèmes)'],
            ['libelle' => 'Neutre', 'valeur' => '0', 'commentaire' => 'Le projet n\'a pas d\'impact significatif sur les émissions de GES ou les vulnérabilités climatiques'],
            ['libelle' => 'Faible atténuation', 'valeur' => '1', 'commentaire' => 'Le projet contribue modestement à la réduction des émissions de GES'],
            ['libelle' => 'Atténuation importante', 'valeur' => '2', 'commentaire' => 'Le projet contribue significativement à la réduction des émissions de GES'],
            ['libelle' => 'Atténuation forte', 'valeur' => '3', 'commentaire' => 'Le projet contribue fortement à la réduction des émissions de GES']
        ];

        foreach ($notationsAttenuation as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereAttenuation->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

        // Critère Adaptation
        $critereAdaptation = \App\Models\Critere::firstOrCreate([
            'intitule' => 'Adaptation',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 3.9,
            'commentaire' => 'La capacité du projet à réduire la vulnérabilité aux impacts du changement climatique et à renforcer la résilience des communautés et écosystèmes.',
            'is_mandatory' => true
        ]);

        // Notations pour Adaptation
        $notationsAdaptation = [
            ['libelle' => 'Négatif', 'valeur' => '-3', 'commentaire' => 'Le projet augmente la vulnérabilité aux changements climatiques'],
            ['libelle' => 'Neutre', 'valeur' => '0', 'commentaire' => 'Le projet n\'a pas d\'impact sur la vulnérabilité climatique'],
            ['libelle' => 'Faible adaptation', 'valeur' => '1', 'commentaire' => 'Le projet contribue modestement à l\'adaptation au changement climatique'],
            ['libelle' => 'Adaptation importante', 'valeur' => '2', 'commentaire' => 'Le projet contribue significativement à l\'adaptation au changement climatique'],
            ['libelle' => 'Adaptation forte', 'valeur' => '3', 'commentaire' => 'Le projet contribue fortement à l\'adaptation au changement climatique']
        ];

        foreach ($notationsAdaptation as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereAdaptation->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }
    }
}
