<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CibleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void

    {
        $cibles = [
            "1.1" => "D’ici à 2030, éliminer complètement l’extrême pauvreté dans le monde entier (vivre avec moins de 1,25 dollar par jour).",
            "1.2" => "D’ici à 2030, réduire de moitié au moins la proportion d’hommes, de femmes et d’enfants de tout âge vivant dans la pauvreté sous toutes ses formes, selon la définition nationale.",
            "1.3" => "Mettre en place des systèmes et mesures de protection sociale pour tous, y compris des socles de protection sociale, et faire en sorte qu’une part importante des pauvres en bénéficient d’ici à 2030.",
            "1.4" => "D’ici à 2030, garantir à tous un accès égal aux ressources économiques, aux services de base, à la propriété foncière, à l’héritage, aux ressources naturelles, à la technologie et aux services financiers, y compris la microfinance.",
            "1.5" => "D’ici à 2030, renforcer la résilience des pauvres et des personnes vulnérables face aux catastrophes économiques, sociales ou environnementales.",
            "1.a" => "Garantir une mobilisation importante de ressources, y compris par la coopération internationale, pour mettre fin à la pauvreté sous toutes ses formes.",
            "1.b" => "Mettre en place des politiques de développement axées sur les pauvres et sensibles à la dimension de genre, aux niveaux national, régional et international.",
            "2.1" => "D’ici à 2030, éliminer la faim et faire en sorte que chacun ait accès toute l’année à une alimentation saine, nutritive et suffisante.",
            "2.2" => "D’ici à 2030, mettre fin à toutes les formes de malnutrition, notamment chez les enfants de moins de 5 ans, les adolescentes, les femmes enceintes et les personnes âgées.",
            "2.3" => "D’ici à 2030, doubler la productivité agricole et les revenus des petits producteurs alimentaires, en assurant l’égalité d’accès aux terres, intrants, services et marchés."
        ];


        foreach ($cibles as $key => $cible) {
            // Critère Atténuation
            \App\Models\Cible::firstOrCreate([
                'cible' => $cible,
                "slug" => Str::slug($cible)
            ]);
        }
    }
}
