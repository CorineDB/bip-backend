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
            'ponderation' => 5.0,
            'commentaire' => 'La réduction des émissions de gaz à effet de serre résultant de la mise en œuvre du projet, avec la taxonomie de l\'UE comme guide pour établir un seuil en cas d\'ambiguïté concernant l\'importance de la contribution d\'un projet à la réduction des émissions de GES (par exemple, un projet d\'électricité avec des émissions de cycle de vie >100 g CO2e/kWh doit être classé comme « Faible atténuation » même s\'il prétend le contraire).',
            'is_mandatory' => true
        ]);

        // Notations pour Atténuation
        $notationsAttenuation = [
            ['libelle' => 'Négatif', 'valeur' => '-3', 'commentaire' => 'Le projet aggrave les impacts climatiques en augmentant significativement les émissions de gaz à effet de serre (GES) ou en amplifiant les vulnérabilités environnementales (déforestation, destruction des écosystèmes)'],
            ['libelle' => 'Neutre', 'valeur' => '0', 'commentaire' => 'Le projet n\'a aucun effet significatif sur le climat. Il n\'entraîne ni réduction ni augmentation des émissions ou des risques environnementaux.'],
            ['libelle' => 'Moyenne', 'valeur' => '3', 'commentaire' => 'Le projet contribue modérément à l\'atténuation des impacts climatiques, par exemple en réduisant les émissions de GES ou en améliorant l\'efficacité énergétique de manière mesurable.'],
            ['libelle' => 'Élevée', 'valeur' => '5', 'commentaire' => 'Le projet a un impact très positif sur le climat, notamment en réduisant fortement les émissions, en favorisant les énergies renouvelables, ou en améliorant la résilience climatique (adaptation aux risques).']
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
            'ponderation' => 5.0,
            'commentaire' => 'Les pertes évitées directement ou indirectement attribuables à la mise en œuvre d\'un projet, conférant ainsi une résilience dans les secteurs vulnérables au climat (par exemple, l\'agriculture, l\'eau, l\'énergie, la santé, etc.).',
            'is_mandatory' => true
        ]);

        // Notations pour Adaptation
        $notationsAdaptation = [
            ['libelle' => 'Négatif', 'valeur' => '-3', 'commentaire' => 'Le projet aggrave les impacts climatiques en augmentant significativement les émissions de gaz à effet de serre (GES) ou en amplifiant les vulnérabilités environnementales (déforestation, destruction des écosystèmes)'],
            ['libelle' => 'Neutre', 'valeur' => '0', 'commentaire' => 'Le projet n\'a aucun effet significatif sur le climat. Il n\'entraîne ni réduction ni augmentation des émissions ou des risques environnementaux.'],
            ['libelle' => 'Moyenne', 'valeur' => '3', 'commentaire' => 'Le projet contribue modérément à l\'atténuation des impacts climatiques, par exemple en réduisant les émissions de GES ou en améliorant l\'efficacité énergétique de manière mesurable.'],
            ['libelle' => 'Élevée', 'valeur' => '5', 'commentaire' => 'Le projet a un impact très positif sur le climat, notamment en réduisant fortement les émissions, en favorisant les énergies renouvelables, ou en améliorant la résilience climatique (adaptation aux risques).']
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

        // Critère Contribution à l'objectif CDN
        $critereCDN = \App\Models\Critere::firstOrCreate([
            'intitule' => 'Contribution à l\'objectif CDN',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 3.0,
            'commentaire' => 'Les idées de projet étant générées au niveau du ministère sectoriel, il est important d\'articuler dès le début le potentiel d\'un projet à contribuer aux objectifs d\'engagement définis dans les contributions déterminées au niveau national du Bénin.',
            'is_mandatory' => true
        ]);

        // Notations pour CDN
        $notationsCDN = [
            ['libelle' => 'Neutre', 'valeur' => '0', 'commentaire' => 'Le projet n\'a aucune contribution notable, positive ou négative, aux objectifs climatiques des CDN. Ses activités sont neutres en matière de climat, n\'ayant ni effet direct sur les émissions, ni impact sur la résilience climatique.'],
            ['libelle' => 'Faible', 'valeur' => '1', 'commentaire' => 'Le projet a une faible contribution aux objectifs climatiques. Il intègre seulement des actions marginales en matière de réduction des émissions de GES ou d\'adaptation, sans effet substantiel.'],
            ['libelle' => 'Moyenne', 'valeur' => '2', 'commentaire' => 'Le projet contribue modérément à l\'atténuation des impacts climatiques, par exemple en réduisant les émissions de GES ou en améliorant l\'efficacité énergétique de manière mesurable.'],
            ['libelle' => 'Élevée', 'valeur' => '3', 'commentaire' => 'Le projet contribue significativement aux objectifs de réduction des émissions ou d\'adaptation climatique définis dans les CDN du Bénin.']
        ];

        foreach ($notationsCDN as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereCDN->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

        // Critère Changement transformationnel
        $critereTransformationnel = \App\Models\Critere::firstOrCreate([
            'intitule' => 'Changement transformationnel',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 3.0,
            'commentaire' => 'Capacité du projet à provoquer un changement de paradigme avec des impacts et des externalités positives plus larges. Le cadre suivant du GCF (Fonds vert pour le climat) est utile pour évaluer le changement transformationnel.',
            'is_mandatory' => true
        ]);

        // Notations pour Changement transformationnel
        $notationsTransformationnel = [
            ['libelle' => 'Neutre', 'valeur' => '0', 'commentaire' => 'Le projet n\'a aucun effet significatif sur le changement climatique, ni en termes de réduction des émissions ni d\'adaptation. Il n\'entraîne ni amélioration ni dégradation des conditions climatiques actuelles.'],
            ['libelle' => 'Faible', 'valeur' => '1', 'commentaire' => 'Le projet intègre des mesures climatiques, mais celles-ci sont marginales ou limitées dans leur portée. L\'impact sur le long terme ou à grande échelle reste faible.'],
            ['libelle' => 'Moyenne', 'valeur' => '2', 'commentaire' => 'Le projet contribue de manière significative à la réduction des émissions ou à l\'adaptation climatique, mais ses effets sont limités à un secteur ou à une région spécifique. Le changement est substantiel mais pas entièrement transformateur.'],
            ['libelle' => 'Élevée', 'valeur' => '3', 'commentaire' => 'Le projet entraîne un changement profond et durable vers une économie faible en carbone ou résiliente au climat. Il transforme radicalement les pratiques existantes et peut servir de modèle pour d\'autres initiatives similaires.']
        ];

        foreach ($notationsTransformationnel as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereTransformationnel->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }
    }
}
