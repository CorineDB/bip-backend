<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChecklistMesureAdapation extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorieCritere = \App\Models\CategorieCritere::firstOrCreate([
            'slug' => 'checklist-mesure-adaption',
        ], [
            'type' => "Checklist des mesures d’adaptation - CONTRÔLE DES D'ADAPTATIONS POUR LES PROJETS À HAUT RISQUE",
            'slug' => 'checklist-mesure-adaption',
            'is_mandatory' => true
        ]);

        // Notations pour Impact climatique (1-10)
        $options_notation = [
            ['libelle' => 'Très faible',    'valeur' => 1,  'commentaire' => 'Aucun impact positif sur le climat, peut même avoir des effets négatifs'],
            ['libelle' => 'Faible',         'valeur' => 2,  'commentaire' => 'Impact climatique minimal ou négligeable'],
            ['libelle' => 'Insuffisant',    'valeur' => 3,  'commentaire' => 'Quelques mesures climatiques mais insuffisantes'],
            ['libelle' => 'Modéré',         'valeur' => 4,  'commentaire' => 'Impact climatique modéré avec quelques bénéfices identifiables'],
            ['libelle' => 'Acceptable',     'valeur' => 5,  'commentaire' => 'Contribution acceptable aux objectifs climatiques'],
            ['libelle' => 'Bon',            'valeur' => 6,  'commentaire' => 'Bonne contribution à la réduction des GES ou à l\'adaptation'],
            ['libelle' => 'Très bon',       'valeur' => 7,  'commentaire' => 'Impact climatique significatif et mesurable'],
            ['libelle' => 'Élevé',          'valeur' => 8,  'commentaire' => 'Forte contribution aux objectifs climatiques nationaux'],
            ['libelle' => 'Très élevé',     'valeur' => 9,  'commentaire' => 'Impact climatique majeur avec transformation sectorielle'],
            ['libelle' => 'Exceptionnel',   'valeur' => 10, 'commentaire' => 'Impact climatique exceptionnel, effet transformateur à grande échelle']
        ];

        foreach ($options_notation as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }


        // Critère Impact climatique
        $critereImpactClimatique = \App\Models\Critere::firstOrCreate([
            'intitule' => 'Impact climatique',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 25.0,
            'commentaire' => 'Les projets sont classés selon leur contribution aux engagements climatiques du Bénin, notamment en matière de réduction des émissions de gaz à effet de serre (GES) et de résilience climatique. Un projet ayant un effet « carbone positif » obtiendra une note supérieure à un projet neutre ou négatif.',
            'is_mandatory' => true
        ]);



        // Critère Rentabilité économique
        $critereRentabilite = \App\Models\Critere::firstOrCreate([
            'intitule' => 'Rentabilité économique',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 25.0,
            'commentaire' => 'La viabilité financière du projet est analysée, avec une note attribuée en fonction de sa capacité à générer des retombées économiques et à optimiser l\'utilisation des ressources publiques.',
            'is_mandatory' => true
        ]);

        /*
        // Notations pour Rentabilité économique (1-10)
        $notationsRentabilite = [
            ['libelle' => 'Très faible',    'valeur' => 1, 'commentaire' => 'Rentabilité très faible, risque financier élevé'],
            ['libelle' => 'Faible',         'valeur' => 2, 'commentaire' => 'Rentabilité faible, retour sur investissement incertain'],
            ['libelle' => 'Insuffisant',    'valeur' => 3, 'commentaire' => 'Rentabilité insuffisante pour justifier l\'investissement'],
            ['libelle' => 'Modéré',         'valeur' => 4, 'commentaire' => 'Rentabilité modérée avec quelques bénéfices économiques'],
            ['libelle' => 'Acceptable',     'valeur' => 5, 'commentaire' => 'Rentabilité acceptable, équilibre coût-bénéfice'],
            ['libelle' => 'Bon',            'valeur' => 6, 'commentaire' => 'Bonne rentabilité avec retombées économiques positives'],
            ['libelle' => 'Très bon',       'valeur' => 7, 'commentaire' => 'Très bonne rentabilité, optimisation des ressources publiques'],
            ['libelle' => 'Élevé',          'valeur' => 8, 'commentaire' => 'Rentabilité élevée avec impact économique significatif'],
            ['libelle' => 'Très élevé',     'valeur' => 9, 'commentaire' => 'Très haute rentabilité, générateur de revenus importants'],
            ['libelle' => 'Exceptionnel',   'valeur' => 10, 'commentaire' => 'Rentabilité exceptionnelle, transformation économique sectorielle']
        ];

        foreach ($notationsRentabilite as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereRentabilite->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }*/

        // Critère Impact social
        $critereImpactSocial = \App\Models\Critere::firstOrCreate([
            'intitule' => 'Impact social',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 25.0,
            'commentaire' => 'Les dimensions sociales, incluant l\'inclusion du genre et la réduction des inégalités, sont considérées. Un projet qui répond aux objectifs sociaux nationaux, en intégrant par exemple des programmes d\'égalité, recevra une note élevée.',
            'is_mandatory' => true
        ]);

        // Notations pour Impact social (1-10)
        /*$notationsImpactSocial = [
            ['libelle' => 'Très faible',    'valeur' => 1, 'commentaire' => 'Aucun impact social positif, peut créer des inégalités'],
            ['libelle' => 'Faible',         'valeur' => 2, 'commentaire' => 'Impact social limité, bénéfices sociaux marginaux'],
            ['libelle' => 'Insuffisant',    'valeur' => 3, 'commentaire' => 'Impact social insuffisant, inclusion limitée'],
            ['libelle' => 'Modéré',         'valeur' => 4, 'commentaire' => 'Impact social modéré avec quelques bénéfices identifiables'],
            ['libelle' => 'Acceptable',     'valeur' => 5, 'commentaire' => 'Impact social acceptable, prise en compte basique du genre'],
            ['libelle' => 'Bon',            'valeur' => 6, 'commentaire' => 'Bon impact social, inclusion du genre et réduction d\'inégalités'],
            ['libelle' => 'Très bon',       'valeur' => 7, 'commentaire' => 'Très bon impact social, forte inclusion et cohésion sociale'],
            ['libelle' => 'Élevé',          'valeur' => 8, 'commentaire' => 'Impact social élevé, transformation des conditions sociales'],
            ['libelle' => 'Très élevé',     'valeur' => 9, 'commentaire' => 'Impact social majeur, amélioration significative du bien-être'],
            ['libelle' => 'Exceptionnel',   'valeur' => 10, 'commentaire' => 'Impact social exceptionnel, transformation sociale profonde']
        ];

        foreach ($notationsImpactSocial as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereImpactSocial->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }*/

        // Critère Compatibilité technique
        $critereCompatibiliteTechnique = \App\Models\Critere::firstOrCreate([
            'intitule' => 'Compatibilité technique',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 25.0,
            'commentaire' => 'L\'AMC prend en compte la faisabilité technique du projet, incluant les compétences et infrastructures nécessaires à sa mise en œuvre.',
            'is_mandatory' => true
        ]);

        // Notations pour Compatibilité technique (1-10)
        /*$notationsCompatibiliteTechnique = [
            ['libelle' => 'Très faible',    'valeur' => 1, 'commentaire' => 'Faisabilité technique très faible, défis technologiques majeurs'],
            ['libelle' => 'Faible',         'valeur' => 2, 'commentaire' => 'Faisabilité technique limitée, manque de compétences/infrastructures'],
            ['libelle' => 'Insuffisant',    'valeur' => 3, 'commentaire' => 'Compatibilité technique insuffisante, risques d\'implémentation'],
            ['libelle' => 'Modéré',         'valeur' => 4, 'commentaire' => 'Faisabilité technique modérée, quelques défis à surmonter'],
            ['libelle' => 'Acceptable',     'valeur' => 5, 'commentaire' => 'Compatibilité technique acceptable, ressources disponibles'],
            ['libelle' => 'Bon',            'valeur' => 6, 'commentaire' => 'Bonne faisabilité technique, compétences et infrastructures adaptées'],
            ['libelle' => 'Très bon',       'valeur' => 7, 'commentaire' => 'Très bonne compatibilité, maîtrise technique avérée'],
            ['libelle' => 'Élevé',          'valeur' => 8, 'commentaire' => 'Faisabilité technique élevée, expertise et outils disponibles'],
            ['libelle' => 'Très élevé',     'valeur' => 9, 'commentaire' => 'Très haute compatibilité technique, technologies éprouvées'],
            ['libelle' => 'Exceptionnel',   'valeur' => 10, 'commentaire' => 'Faisabilité technique exceptionnelle, innovation technologique']
        ];

        foreach ($notationsCompatibiliteTechnique as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereCompatibiliteTechnique->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }*/
    }
}
