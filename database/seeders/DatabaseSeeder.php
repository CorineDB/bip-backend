<?php

namespace Database\Seeders;

use App\Models\Arrondissement;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
            VillageSeeder::class,
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
            GrilleAMCSeeder::class

            /*
            OrganisationsSeeder::class,
            PersonnesSeeder::class,

            // Then, seed the new categorized users system
            CategoriesUtilisateursSeeder::class,
            UpdateUsersWithCategoriesSeeder::class,*/
        ]);
    }
}
