<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class PersonnesSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        // Get some organisation IDs to assign persons to
        $organisationIds = DB::table('organisations')->pluck('id')->toArray();

        $personnes = [
            // Super Admin
            [
                'nom' => 'Administrateur',
                'prenom' => 'Système',
                'poste' => 'Super Administrateur',
                'organismeId' => $organisationIds[0] ?? 1
            ],

            // Ministère du Plan
            [
                'nom' => 'Mukendi',
                'prenom' => 'Jean-Pierre',
                'poste' => 'Ministre du Plan',
                'organismeId' => 2 // Ministère du Plan
            ],
            [
                'nom' => 'Tshimanga',
                'prenom' => 'Patrick',
                'poste' => 'Directeur de la Planification',
                'organismeId' => 2
            ],

        ];

        // Add some additional random persons
        for ($i = 0; $i < 20; $i++) {
            $personnes[] = [
                'nom' => $faker->lastName,
                'prenom' => $faker->firstName,
                'poste' => $faker->randomElement([
                    'Directeur', 'Chef de Service', 'Analyste', 'Consultant',
                    'Coordonnateur', 'Gestionnaire', 'Spécialiste', 'Responsable',
                    'Chargé de Programme', 'Expert', 'Conseiller', 'Assistant'
                ]),
                'organismeId' => $faker->randomElement($organisationIds)
            ];
        }

        foreach ($personnes as $personne) {
            DB::table('personnes')->updateOrInsert(
                [
                    'nom' => $personne['nom'],
                    'prenom' => $personne['prenom'],
                    'organismeId' => $personne['organismeId']
                ],
                [
                    'nom' => $personne['nom'],
                    'prenom' => $personne['prenom'],
                    'poste' => $personne['poste'],
                    'organismeId' => $personne['organismeId'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }
    }
}