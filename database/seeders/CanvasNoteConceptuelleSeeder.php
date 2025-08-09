<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CanvasNoteConceptuelleSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Création du canevas de rédaction d\'une note conceptuelle...');

        $canevasData = [
            [
                'section' => 'I. CONTEXTE ET JUSTIFICATION',
                'ordre' => 1,
                'champs' => [
                    [
                        'nom' => 'Contexte et justification',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Présentation du contexte général et justification de la nécessité du projet',
                        'ordre' => 1
                    ]
                ]
            ],
            [
                'section' => 'II. OBJECTIFS DU PROJET',
                'ordre' => 2,
                'champs' => [
                    [
                        'nom' => 'Objectifs du projet',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Objectifs généraux et spécifiques du projet',
                        'ordre' => 1
                    ]
                ]
            ],
            [
                'section' => 'III. RÉSULTATS ATTENDUS DU PROJET',
                'ordre' => 3,
                'champs' => [
                    [
                        'nom' => 'Résultats attendus du projet',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Description des résultats escomptés du projet',
                        'ordre' => 1
                    ]
                ]
            ],
            [
                'section' => 'IV. DÉMARCHE DE CONDUITE DU PROCESSUS D\'ÉLABORATION DU PROJET',
                'ordre' => 4,
                'champs' => [
                    [
                        'nom' => 'Démarche administrative',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Description de la démarche administrative pour l\'élaboration du projet',
                        'ordre' => 1
                    ],
                    [
                        'nom' => 'Démarche technique',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Description de la démarche technique pour l\'élaboration du projet',
                        'ordre' => 2
                    ]
                ]
            ],
            [
                'section' => 'V. PARTIES PRENANTES',
                'ordre' => 5,
                'champs' => [
                    [
                        'nom' => 'Parties prenantes',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Identification et rôles des différentes parties prenantes du projet',
                        'ordre' => 1
                    ]
                ]
            ],
            [
                'section' => 'VI. LES LIVRABLES DU PROCESSUS D\'ÉLABORATION DU PROJET',
                'ordre' => 6,
                'champs' => [
                    [
                        'nom' => 'Les livrables du processus d\'élaboration du projet',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Liste et description des livrables attendus du processus d\'élaboration',
                        'ordre' => 1
                    ]
                ]
            ],
            [
                'section' => 'VII. COHÉRENCE DU PROJET AVEC LE PAG OU LA STRATÉGIE SECTORIELLE',
                'ordre' => 7,
                'champs' => [
                    [
                        'nom' => 'Faire le lien entre le cadre stratégique et le cadre programmatique',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Démonstration de la cohérence du projet avec le PAG ou la stratégie sectorielle',
                        'ordre' => 1
                    ]
                ]
            ],
            [
                'section' => 'VIII. PILOTAGE ET GOUVERNANCE DU PROJET',
                'ordre' => 8,
                'champs' => [
                    [
                        'nom' => 'Pilotage et gouvernance du projet',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Description du système de pilotage et de gouvernance du projet',
                        'ordre' => 1
                    ]
                ]
            ],
            [
                'section' => 'IX. CHRONOGRAMME DU PROCESSUS',
                'ordre' => 9,
                'champs' => [
                    [
                        'nom' => 'Chronogramme du processus',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Planning détaillé du processus d\'élaboration du projet',
                        'ordre' => 1
                    ]
                ]
            ],
            [
                'section' => 'X. BUDGET ET SOURCES DE FINANCEMENT DU PROJET',
                'ordre' => 10,
                'champs' => [
                    [
                        'nom' => 'Budget détaillé du processus',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Budget détaillé pour le processus d\'élaboration',
                        'ordre' => 1
                    ],
                    [
                        'nom' => 'Coût estimatif du projet',
                        'type' => 'number',
                        'obligatoire' => true,
                        'description' => 'Coût total estimé du projet en FCFA',
                        'ordre' => 2
                    ],
                    [
                        'nom' => 'Sources de financement',
                        'type' => 'textarea',
                        'obligatoire' => true,
                        'description' => 'Identification et description des sources de financement',
                        'ordre' => 3
                    ]
                ]
            ]
        ];

        DB::beginTransaction();
        
        try {
            foreach ($canevasData as $sectionData) {
                $sectionId = DB::table('canvas_note_conceptuelle_sections')->insertGetId([
                    'nom' => $sectionData['section'],
                    'ordre' => $sectionData['ordre'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                foreach ($sectionData['champs'] as $champ) {
                    DB::table('canvas_note_conceptuelle_champs')->insert([
                        'section_id' => $sectionId,
                        'nom' => $champ['nom'],
                        'type' => $champ['type'],
                        'obligatoire' => $champ['obligatoire'],
                        'description' => $champ['description'],
                        'ordre' => $champ['ordre'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            DB::commit();
            $this->command->info('✅ Canevas de note conceptuelle créé avec succès !');
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Erreur lors de la création du canevas : ' . $e->getMessage());
        }
    }
}