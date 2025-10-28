<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class GrilleEvaluationPertinenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorieCritere = \App\Models\CategorieCritere::firstOrCreate([
            'slug' => 'grille-evaluation-pertinence-idee-projet',
        ], [
            'type' => 'Outil d\'Évaluation de pertinence d\'une idee de projet',
            'slug' => 'grille-evaluation-pertinence-idee-projet',
            'is_mandatory' => true
        ]);

        // Critère  INNOVATION (solution innovante, approche nouvellse) 
        $critereInnovation = \App\Models\Critere::updateOrCreate([
            'intitule' => ' INNOVATION (solution innovante, approche nouvellse) ',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => '30',
            'commentaire' => 'Évaluation du degré d\'innovation du projet proposé et nouveauté de l\'approche',
            'is_mandatory' => true
        ]);

        // Notations pour  INNOVATION (solution innovante, approche nouvellse) 
        $notationsInnovation = [
            ['libelle' => 'Nul', 'valeur' => '0', 'commentaire' => 'Le projet n\'est pas innovant'],
            ['libelle' => 'Faible', 'valeur' => '1', 'commentaire' => 'Le projet comporte quelques éléments nouveaux, mais manque d\'originalité'],
            ['libelle' => 'Moyenne', 'valeur' => '2', 'commentaire' => 'Le projet propose une approche unique et intéressante pour résoudre un problème'],
            ['libelle' => 'Élevée', 'valeur' => '3', 'commentaire' => 'Le projet est véritablement révolutionnaire avec un haut degré de nouveauté'],
        ];

        foreach ($notationsInnovation as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereInnovation->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

        // Critère  CHANGEMENTS TRANSFORMATIONNELS ATTENDUS
        $critereChangementsTransformationnelsAttendus = \App\Models\Critere::updateOrCreate([
            'intitule' => ' CHANGEMENTS TRANSFORMATIONNELS ATTENDUS',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => '15',
            'commentaire' => 'Potentiel du projet à conduire un changement de paradigme dans une industrie ou un secteur particulier',
            'is_mandatory' => true
        ]);

        // Notations pour  CHANGEMENTS TRANSFORMATIONNELS ATTENDUS
        $notationsChangementsTransformationnelsAttendus = [
            ['libelle' => 'Nul', 'valeur' => '0', 'commentaire' => 'Le projet représente pas d’avancée par rapport au statu quo'],
            ['libelle' => 'Faible', 'valeur' => '1', 'commentaire' => 'Le projet a peu de potentiel pour avoir un impact sur les activités habituelles'],
            ['libelle' => 'Moyenne', 'valeur' => '2', 'commentaire' => 'Le projet a de fortes chances de changer le statu quo et de modifier les comportements'],
            ['libelle' => 'Élevée', 'valeur' => '3', 'commentaire' => 'Le projet a le potentiel de provoquer un changement radical dans une industrie ou un secteur'],
        ];

        foreach ($notationsChangementsTransformationnelsAttendus as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereChangementsTransformationnelsAttendus->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

        // Critère ALIGNEMENT AVEC LES PRIORITÉS NATIONALES DE DÉVELOPPEMENT
        $critereAlignementAvecLesPrioritesNationalesDeDeveloppement = \App\Models\Critere::updateOrCreate([
            'intitule' => 'ALIGNEMENT AVEC LES PRIORITÉS NATIONALES DE DÉVELOPPEMENT',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => '20',
            'commentaire' => 'Alignement sur les priorités nationales de développement telles qu\'énoncées dans les principaux plans nationaux de développement et les documents de planification stratégique (e.g. Plan National de Développement 2018-25)',
            'is_mandatory' => true
        ]);

        // Notations pour ALIGNEMENT AVEC LES PRIORITÉS NATIONALES DE DÉVELOPPEMENT
        $notationsAlignementAvecLesPrioritesNationalesDeDeveloppement = [
            ['libelle' => 'Non', 'valeur' => '0', 'commentaire' => 'Le projet n\'est pas aligné sur les priorités nationales de développement'],
            ['libelle' => 'Oui', 'valeur' => '3', 'commentaire' => 'Le projet est aligné sur les priorités nationales de développement'],
        ];

        foreach ($notationsAlignementAvecLesPrioritesNationalesDeDeveloppement as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereAlignementAvecLesPrioritesNationalesDeDeveloppement->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

        // Critère COHERENCE AVEC LE CADRE STRATEGIQUE ET LE CADRE PROGRAMMATIQUE DU SECTEUR (cohérence interne) 
        $critereCoherenceAvecLeCadreStrategiqueEtLeCadreProgrammatiqueDuSecteur = \App\Models\Critere::updateOrCreate([
            'intitule' => 'COHERENCE AVEC LE CADRE STRATEGIQUE ET LE CADRE PROGRAMMATIQUE DU SECTEUR (cohérence interne) ',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => '20',
            'commentaire' => 'Évaluation de haut niveau de l\'adéquation du projet avec le programme du ministère sectoriel',
            'is_mandatory' => true
        ]);

        // Notations pour COHERENCE AVEC LE CADRE STRATEGIQUE ET LE CADRE PROGRAMMATIQUE DU SECTEUR (cohérence interne) 
        $notationsCoherenceAvecLeCadreStrategiqueEtLeCadreProgrammatiqueDuSecteur = [
            ['libelle' => 'Non', 'valeur' => '0', 'commentaire' => 'Le projet ne correspond pas bien à l’agenda du ministère sectoriel'],
            ['libelle' => 'Oui', 'valeur' => '3', 'commentaire' => 'Le projet s\'inscrit bien dans l\'agenda du ministère sectoriel'],
        ];

        foreach ($notationsCoherenceAvecLeCadreStrategiqueEtLeCadreProgrammatiqueDuSecteur as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereCoherenceAvecLeCadreStrategiqueEtLeCadreProgrammatiqueDuSecteur->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

        // Critère ALIGNEMENT SUR LES ODD (cohérence externe avec cibles et objectifs domestiqués) 
        $critereAlignementSurLesOdd = \App\Models\Critere::updateOrCreate([
            'intitule' => 'ALIGNEMENT SUR LES ODD (cohérence externe avec cibles et objectifs domestiqués) ',
            'categorie_critere_id' => $categorieCritere->id
        ], [
            'ponderation' => '15',
            'commentaire' => 'Contribution du projet à la réalisation des objectifs de développement durable tels que définis dans la stratégie nationale de développement du Bénin',
            'is_mandatory' => true
        ]);

        // Notations pour ALIGNEMENT SUR LES ODD (cohérence externe avec cibles et objectifs domestiqués) 
        $notationsAlignementSurLesOdd = [
            ['libelle' => 'Non', 'valeur' => '0', 'commentaire' => 'Le projet ne contribue pas à la réalisation des objectifs de développement durable du Bénin'],
            ['libelle' => 'Oui', 'valeur' => '3', 'commentaire' => 'Le projet soutient la réalisation des objectifs de développement durable du Bénin'],
        ];

        foreach ($notationsAlignementSurLesOdd as $notation) {
            \App\Models\Notation::firstOrCreate([
                'libelle' => $notation['libelle'],
                'critere_id' => $critereAlignementSurLesOdd->id,
                'categorie_critere_id' => $categorieCritere->id
            ], [
                'valeur' => $notation['valeur'],
                'commentaire' => $notation['commentaire']
            ]);
        }

    }
}
