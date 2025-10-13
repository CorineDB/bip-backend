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
        // Récupère toutes les valeurs existantes de type
        $existingStatuts = DB::table('rapports')->distinct()->pluck('type')->toArray();

        // Combine avec les valeurs officielles de SousPhaseIdee
        $allowedStatuts = array_unique(array_merge($existingStatuts, ['prefaisabilite', 'faisabilite', 'faisabilite-preliminaire', 'evaluation_ex_ante']));

        // Supprimer la contrainte existante
        DB::statement('ALTER TABLE rapports DROP CONSTRAINT IF EXISTS rapports_type_check');

        // Ajouter la nouvelle contrainte qui couvre les valeurs existantes + les nouvelles
        DB::statement("ALTER TABLE rapports ADD CONSTRAINT rapports_type_check CHECK (type::text = ANY (ARRAY['" . implode("','", $allowedStatuts) . "']::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
