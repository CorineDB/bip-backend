<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OddSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $odds = [
            "Pas de pauvreté",
            "Faim « zéro »",
            "Bonne santé et bien-être",
            "Éducation de qualité",
            "Égalité entre les sexes",
            "Eau propre et assainissement",
            "Énergie propre et d'un coût abordable",
            "Travail décent et croissance économique",
            "Industrie, innovation et infrastructure",
            "Inégalités réduites",
            "Villes et communautés durables",
            "Consommation et production responsables",
            "Mesures relatives à la lutte contre les changements climatiques",
            "Vie aquatique",
            "Vie terrestre",
            "Paix, justice et institutions efficaces",
            "Partenariats pour la réalisation des objectifs"
        ];
        \App\Models\Odd::truncate();
        foreach ($odds as $key => $odd) {
            // Critère Atténuation
            \App\Models\Odd::firstOrCreate(
                ['odd' => $odd],
                [
                    "slug" => Str::slug($odd)
                ]
            );
        }
    }
}
