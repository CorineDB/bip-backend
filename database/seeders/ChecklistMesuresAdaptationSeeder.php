<?php

namespace Database\Seeders;

use App\Helpers\SlugHelper;
use App\Models\Secteur;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChecklistMesuresAdaptationSeeder extends Seeder
{
    protected $criteresEtNotationsParSecteurs = [];

    protected $secteurs;

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

        foreach (["Agriculture", "Energie", "Eau"] as $key => $value) {

            $this->secteurs[$key] = Secteur::firstOrCreate([
                'slug' => "agriculture",
                'type' => "secteur"
            ], [
                'nom' => $value,
                'slug' => SlugHelper::generate($value),
                'type' => "secteur",
                'description' => $value
            ])->id;
        }


        // Critère Atténuation
        $critereChaleur = \App\Models\Critere::updateOrCreate([
            'intitule' => 'Chaleur',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => 25,
            'commentaire' => '',
            'is_mandatory' => true
        ]);

        // Notations pour Atténuation
        $notationsChaleurParSecteur = [
            [
                ['libelle' => 'Gestion des ressources en eau', 'valeur' => 'gestion-des-ressources-en-eau', 'commentaire' => ''],
                ['libelle' => 'Irrigation à haut rendement', 'valeur' => 'irrigation-a-haut-rendement', 'commentaire' => ""],
                ['libelle' => 'Exploitation et maintenance des exploitations agricoles', 'valeur' => 'exploitation-et-maintenance-des-exploitations-agricoles', 'commentaire' => ""],
                ['libelle' => 'Sélection végétale', 'valeur' => 'sélection-vegetale', 'commentaire' => ""]

            ],
            [
                ['libelle' => 'Systèmes de refroidissement efficaces', 'valeur' => 'gestion-des-ressources-en-eau', 'commentaire' => ''],
                ['libelle' => 'Composants éoliens/solaires/TIC résistants à la chaleur', 'valeur' => 'irrigation-a-haut-rendement', 'commentaire' => ""],
                ['libelle' => 'Choisissez des emplacements plus frais', 'valeur' => 'exploitation-et-maintenance-des-exploitations-agricoles', 'commentaire' => ""],
                ['libelle' => 'Sélection végétale', 'valeur' => 'sélection-vegetale', 'commentaire' => ""]

            ],
            [
                ['libelle' => "Comptage et tarifs de l'eau", 'valeur' => 'gestion-des-ressources-en-eau', 'commentaire' => ''],
                ['libelle' => "Gestion de la demande / Réduction de l'ENC", 'valeur' => 'irrigation-a-haut-rendement', 'commentaire' => ""],
                ['libelle' => "Réutilisation et dessalement de l'eau", 'valeur' => 'exploitation-et-maintenance-des-exploitations-agricoles', 'commentaire' => ""],
                ['libelle' => "Recharge des aquifères à l'aide d'eau recyclée", 'valeur' => 'sélection-vegetale', 'commentaire' => ""]

            ]
        ];

        foreach ($notationsChaleurParSecteur as $secteurNotations) {

            $notations = isset($secteurNotations["notations"]) ? $secteurNotations["notations"] : [];

            foreach ($notations as $notation) {
                \App\Models\Notation::firstOrCreate([
                    'libelle' => $notation['libelle'],
                    'secteur_id' => $critereChaleur->id,
                    'critere_id' => $critereChaleur->id,
                    'categorie_critere_id' => $categorieCritere->id
                ], [
                    'valeur' => $notation['valeur'],
                    'commentaire' => $notation['commentaire']
                ]);
            }
        }
    }
}
