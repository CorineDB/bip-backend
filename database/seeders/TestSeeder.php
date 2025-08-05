<?php

namespace Database\Seeders;

use App\Models\Evaluation;
use App\Models\IdeeProjet;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("evaluations")->truncate();
        Evaluation::create([
            'type_evaluation' => 'climatique',
            'date_debut_evaluation' => "2025-07-31 11:35:08",
            'projetable_type' => IdeeProjet::class,
            'projetable_id' => 14,
            'evaluateur_id' => 1,
            'commentaire' => " ",
            'resultats_evaluation' => [],
            'evaluation' => [],    'created_at' => "2025-07-31 11:35:08",
            'updated_at' => " 2025-07-31 11:35:08"
        ]);
    }
}
