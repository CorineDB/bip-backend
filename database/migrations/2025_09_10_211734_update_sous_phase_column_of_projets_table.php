<?php

use App\Enums\SousPhaseIdee;
use App\Enums\StatutIdee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Récupère toutes les valeurs existantes de sous_phase
        $existingStatuts = DB::table('projets')->distinct()->pluck('sous_phase')->toArray();

        // Combine avec les valeurs officielles de SousPhaseIdee
        $allowedStatuts = array_unique(array_merge($existingStatuts, SousPhaseIdee::values()));

        // Supprimer la contrainte existante
        DB::statement('ALTER TABLE projets DROP CONSTRAINT IF EXISTS projets_sous_phase_check');

        // Ajouter la nouvelle contrainte qui couvre les valeurs existantes + les nouvelles
        DB::statement("ALTER TABLE projets ADD CONSTRAINT projets_sous_phase_check CHECK (sous_phase::text = ANY (ARRAY['" . implode("','", $allowedStatuts) . "']::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
