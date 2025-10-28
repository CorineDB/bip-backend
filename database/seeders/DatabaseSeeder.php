<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run all seeders in the correct order (respecting foreign key constraints)
        $this->call([
            // First, seed the base tables without foreign key dependencies
            DepartementSeeder::class,
            CommuneSeeder::class,
            ArrondissementSeeder::class,
            VillageGeoJsonUpdateSeeder::class,
            PermissionSeeder::class,
            OddSeeder::class,
            CibleSeeder::class,
            FinancementSeeder::class,
            DomaineCategorieSeeder::class,
            OrganisationsSeeder::class,
            DgpdSeeder::class,
            ProgrammeSeeder::class,
            DomaineCategorieSeeder::class,
            CanevasRedactionFicheIdeeProjet::class,
            GrilleEvaluationPreliminaireSeeder::class,
            GrilleAMCSeeder::class,
            CanvasRedactionNoteConceptuelleSeeder::class,
            ChecklistMesuresAdaptationSeeder::class,

            CanevasAppreciationTdrPrefaisabiliteSeeder::class,
            CanevasAppreciationTdrFaisabiliteSeeder::class,
            CanevasAppreciationNoteConceptuelleSeeder::class,
            ChecklistSuiviAssuranceQualiteRapportFaisabilitePreliminaireSeeder::class,
            CanevasChecklistRapportPrefaisabiliteSeeder::class,

            // Checklists de faisabilité
            ChecklistEtudeFaisabiliteEconomiqueSeeder::class,
            ChecklistEtudeFaisabiliteTechniqueSeeder::class,
            ChecklistEtudeFaisabiliteMarcheSeeder::class,
            ChecklistSuiviAnalyseFaisabiliteFinanciereSeeder::class,
            ChecklistEtudeFaisabiliteOrganisationnelleJuridiqueSeeder::class,
            ChecklistSuiviEtudeImpactEnvironnementaleSocialeSeeder::class,
            ChecklistSuiviAssuranceQualiteRapportFaisabiliteSeeder::class,
        ]);
    }
}
