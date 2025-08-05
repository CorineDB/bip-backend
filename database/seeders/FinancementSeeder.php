<?php

namespace Database\Seeders;

use App\Models\Financement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FinancementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $financements = [
            'Financement budgétaire' =>
            [
                'Budget général de l’État' => [
                    'Ministère de l’Économie et des Finances',
                    'Impôts & taxes',
                    'Trésor public'

                ],

                'Prêt non concessionnel' => [
                    'Marchés financiers régionaux',
                    'Eurobond'
                ],

            ],
            'Financement extérieur' =>
            [
                'Don' => [
                    'Banque mondiale',
                    'Union européenne',
                    'PNUD',
                    'Coopération Suisse'
                ],

                'Prêt concessionnel' => [
                    'FMI',
                    'BAD',
                    'AFD',
                    'Banque Islamique de Développement'
                ],

                'Subvention' => [
                    'UNICEF',
                    'GAVI'
                ]

            ],

            'Financement privé' => [

                'Investissement direct' => [
                    'Investisseurs privés nationaux',
                    'Investisseurs privés étrangers',
                    'Sociétés multinationales'
                ]

            ],

            'Financement mixte' => [

                'PPP (Partenariat Public-Privé)' => [
                    'Concessionnaires privés',
                    'Coentreprises État-Investisseur'

                ],

                'Fonds de garantie' => [
                    'Fonds africain de garantie',
                    'Banques régionales de développement'
                ]

            ],

            'Financement communautaire' => [

                'Contributions volontaires' => [
                    'Diaspora béninoise',
                    'Collectivités locales',
                    'Organisations communautaires'
                ]

            ],

            'Financement innovant' => [

                'Financement basé sur les résultats (FBR)' => [
                    'Fonds vert pour le climat',
                    'Banque mondiale (via indicateurs de performance)'
                ],

                'Mécanismes carbone / obligations vertes' => [
                    'Marchés carbone',
                    'Initiative pour les obligations vertes'
                ]

            ]
        ];

        //DB::table("financements")->truncate();

        // Insertion dans la table `secteurs`
        foreach ($financements as $type => $natures_financement) {
            $typeFin = Financement::firstOrCreate([
                'nom' => $type,
                'nom_usuel' => $type,
                'slug' => Str::slug($type),
                'type' => 'type',
                'financementId' => null,
            ]);

            foreach ($natures_financement as $nature => $sources_financement) {
                $natureFin = Financement::firstOrCreate([
                    'nom' => $nature,
                    'nom_usuel' => $nature,
                    'slug' => Str::slug($nature),
                    'type' => 'nature',
                    'financementId' => $typeFin->id,
                ]);

                foreach ($sources_financement as $source) {
                    Financement::firstOrCreate([
                        'nom' => $source,
                        'nom_usuel' => $source,
                        'slug' => Str::slug($source),
                        'type' => 'source',
                        'financementId' => $natureFin->id,
                    ]);
                }
            }
        }
    }
}
