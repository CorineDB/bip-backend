<?php

use App\Enums\EnumTypeChamp;
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
    { // Récupère toutes les valeurs existantes de statut
        $existingStatuts = DB::table('workflows')->distinct()->pluck('statut')->toArray();

        // Combine avec les valeurs officielles de StatutIdee
        $allowedStatuts = array_unique(array_merge($existingStatuts, StatutIdee::values()));

        // Supprimer la contrainte existante
        DB::statement('ALTER TABLE workflows DROP CONSTRAINT IF EXISTS workflows_statut_check');

        // Ajouter la nouvelle contrainte qui couvre les valeurs existantes + les nouvelles
        DB::statement("ALTER TABLE workflows ADD CONSTRAINT workflows_statut_check CHECK (statut::text = ANY (ARRAY['" . implode("','", $allowedStatuts) . "']::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
