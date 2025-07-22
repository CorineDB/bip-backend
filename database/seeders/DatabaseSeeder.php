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
            OrganisationsSeeder::class,
            PersonnesSeeder::class,

            // Then, seed the new categorized users system
            CategoriesUtilisateursSeeder::class,
            UpdateUsersWithCategoriesSeeder::class,
        ]);



        $this->command->info('All seeders completed successfully!');
        $this->command->info('');
        $this->command->info('=== COMPTES UTILISATEURS CRÉÉS ===');
        $this->command->info('');
        $this->command->info('Super Administrateur:');
        $this->command->info('  Username: superadmin');
        $this->command->info('  Password: SuperAdmin123!');
        $this->command->info('');
        $this->command->info('Responsables Projet (DPAF/Ministère):');
        $this->command->info('  Username: resp.projet.sante | Password: ResponsableProjet123!');
        $this->command->info('  Username: resp.projet.education | Password: ResponsableProjet123!');
        $this->command->info('  Username: resp.projet.agriculture | Password: ResponsableProjet123!');
        $this->command->info('');
        $this->command->info('Responsables Hiérarchiques (Ministère):');
        $this->command->info('  Username: ministre.sante | Password: ResponsableHier123!');
        $this->command->info('  Username: ministre.education | Password: ResponsableHier123!');
        $this->command->info('  Username: ministre.plan | Password: ResponsableHier123!');
        $this->command->info('');
        $this->command->info('DPAF:');
        $this->command->info('  Username: dpaf.plan | Password: DPAF123!');
        $this->command->info('  Username: dpaf.finances | Password: DPAF123!');
        $this->command->info('');
        $this->command->info('Autres rôles:');
        $this->command->info('  Username: cellule.technique.bceco | Password: CelluleTech123!');
        $this->command->info('  Username: analyste.dgpd.1 | Password: AnalysteDGPD123!');
        $this->command->info('  Username: analyste.dgpd.2 | Password: AnalysteDGPD123!');
        $this->command->info('  Username: comite.validation.plan | Password: ComiteValidation123!');
        $this->command->info('  Username: dgpd.coordinateur | Password: DGPD123!');
    }
}
