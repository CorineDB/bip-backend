<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistMesuresAdaptationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer la catégorie critère pour la checklist des mesures d'adaptation
        $categorieCritere = \App\Models\CategorieCritere::firstOrCreate([
            'slug' => 'checklist-mesures-adaptation-haut-risque',
        ], [
            'type' => "Checklist des mesures d'adaptation - CONTRÔLE DES ADAPTATIONS POUR LES PROJETS À HAUT RISQUE",
            'slug' => 'checklist-mesures-adaptation-haut-risque',
            'is_mandatory' => true
        ]);

        // Récupérer tous les secteurs pour créer des sous-critères adaptés
        $secteurs = \App\Models\Secteur::whereNull('secteur_id')->get(); // Grands secteurs uniquement

        // 1. Critère: Évaluation des risques climatiques
        $critereEvaluationRisques = \App\Models\Critere::updateOrCreate([
            'intitule' => 'Évaluation des risques climatiques',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 30,
            'commentaire' => 'Vérification que les risques climatiques spécifiques au secteur ont été identifiés, évalués et que leurs impacts potentiels sont analysés.',
            'is_mandatory' => true
        ]);

        // Créer des sous-critères pour chaque secteur
        foreach ($secteurs as $secteur) {
            \App\Models\Critere::updateOrCreate([
                'intitule' => "Risques climatiques - {$secteur->nom}",
                'critere_id' => $critereEvaluationRisques->id,
                'secteur_id' => $secteur->id
            ], [
                'commentaire' => "Évaluation des risques climatiques spécifiques au secteur {$secteur->nom}",
                'ponderation' => 10,
                'is_mandatory' => true
            ]);
        }

        // Notations pour Évaluation des risques climatiques
        $notationsEvaluationRisques = [
            ['libelle' => 'Non conforme', 'valeur' => '0', 'commentaire' => 'Les risques climatiques n\'ont pas été identifiés ou l\'évaluation est insuffisante'],
            ['libelle' => 'Partiellement conforme', 'valeur' => '1', 'commentaire' => 'Certains risques climatiques sont identifiés mais l\'évaluation manque de profondeur'],
            ['libelle' => 'Conforme', 'valeur' => '2', 'commentaire' => 'Les risques climatiques sont correctement identifiés et évalués'],
            ['libelle' => 'Exemplaire', 'valeur' => '3', 'commentaire' => 'Évaluation exhaustive et détaillée des risques climatiques avec analyse d\'impact approfondie']
        ];

        foreach ($notationsEvaluationRisques as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereEvaluationRisques->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

        // 2. Critère: Mesures d'adaptation proposées
        $critereMesuresAdaptation = \App\Models\Critere::updateOrCreate([
            'intitule' => 'Mesures d\'adaptation proposées',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 35,
            'commentaire' => 'Vérification de la pertinence, de la faisabilité et de l\'efficacité des mesures d\'adaptation proposées pour réduire la vulnérabilité aux changements climatiques.',
            'is_mandatory' => true
        ]);

        // Créer des sous-critères pour chaque secteur
        foreach ($secteurs as $secteur) {
            \App\Models\SousCritere::updateOrCreate([
                'intitule' => "Mesures d'adaptation - {$secteur->nom}",
                'critere_id' => $critereMesuresAdaptation->id,
                'secteur_id' => $secteur->id
            ], [
                'commentaire' => "Mesures d'adaptation spécifiques au secteur {$secteur->nom}",
                'ponderation' => 15,
                'is_mandatory' => true
            ]);
        }

        // Notations pour Mesures d'adaptation
        $notationsMesuresAdaptation = [
            ['libelle' => 'Non conforme', 'valeur' => '0', 'commentaire' => 'Aucune mesure d\'adaptation appropriée ou mesures inadéquates'],
            ['libelle' => 'Partiellement conforme', 'valeur' => '1', 'commentaire' => 'Quelques mesures d\'adaptation proposées mais insuffisantes ou peu détaillées'],
            ['libelle' => 'Conforme', 'valeur' => '2', 'commentaire' => 'Mesures d\'adaptation appropriées et bien définies'],
            ['libelle' => 'Exemplaire', 'valeur' => '3', 'commentaire' => 'Mesures d\'adaptation innovantes, complètes et parfaitement adaptées aux risques identifiés']
        ];

        foreach ($notationsMesuresAdaptation as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereMesuresAdaptation->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

        // 3. Critère: Budget et financement des mesures
        $critereBudgetFinancement = \App\Models\Critere::updateOrCreate([
            'intitule' => 'Budget et financement des mesures',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 20,
            'commentaire' => 'Vérification que le coût des mesures d\'adaptation est correctement budgétisé et que les sources de financement sont identifiées.',
            'is_mandatory' => true
        ]);

        // Notations pour Budget et financement
        $notationsBudgetFinancement = [
            ['libelle' => 'Non conforme', 'valeur' => '0', 'commentaire' => 'Coût des mesures non budgétisé ou sources de financement non identifiées'],
            ['libelle' => 'Partiellement conforme', 'valeur' => '1', 'commentaire' => 'Budget partiel ou sources de financement partiellement identifiées'],
            ['libelle' => 'Conforme', 'valeur' => '2', 'commentaire' => 'Budget complet et sources de financement clairement identifiées'],
            ['libelle' => 'Exemplaire', 'valeur' => '3', 'commentaire' => 'Budget détaillé avec plan de financement diversifié et sécurisé']
        ];

        foreach ($notationsBudgetFinancement as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereBudgetFinancement->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

        // 4. Critère: Plan de mise en œuvre et suivi
        $criterePlanMiseEnOeuvre = \App\Models\Critere::updateOrCreate([
            'intitule' => 'Plan de mise en œuvre et suivi',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 15,
            'commentaire' => 'Vérification de l\'existence d\'un plan détaillé de mise en œuvre des mesures d\'adaptation avec des indicateurs de suivi appropriés.',
            'is_mandatory' => false
        ]);

        // Notations pour Plan de mise en œuvre
        $notationsPlanMiseEnOeuvre = [
            ['libelle' => 'Non conforme', 'valeur' => '0', 'commentaire' => 'Aucun plan de mise en œuvre ou plan très insuffisant'],
            ['libelle' => 'Partiellement conforme', 'valeur' => '1', 'commentaire' => 'Plan de mise en œuvre basique sans indicateurs de suivi détaillés'],
            ['libelle' => 'Conforme', 'valeur' => '2', 'commentaire' => 'Plan de mise en œuvre complet avec indicateurs de suivi appropriés'],
            ['libelle' => 'Exemplaire', 'valeur' => '3', 'commentaire' => 'Plan de mise en œuvre détaillé avec système de monitoring robuste et mécanismes d\'ajustement']
        ];

        foreach ($notationsPlanMiseEnOeuvre as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $criterePlanMiseEnOeuvre->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }
    }
}