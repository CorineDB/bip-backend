<?php

use App\Enums\StatutIdee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Récupère toutes les valeurs existantes de statut
        $existingStatuts = DB::table('workflows')->distinct()->pluck('statut')->toArray();

        // Combine avec les valeurs officielles de StatutIdee
        $allowedStatuts = array_unique(array_merge($existingStatuts, StatutIdee::values()));

        // Supprimer la contrainte existante
        DB::statement('ALTER TABLE workflows DROP CONSTRAINT IF EXISTS workflows_statut_check');

        // Ajouter la nouvelle contrainte avec les valeurs correctes de StatutIdee
        DB::statement("ALTER TABLE workflows ADD CONSTRAINT workflows_statut_check CHECK (statut::text = ANY (ARRAY['" . implode("','", $allowedStatuts) . "']::text[]))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // On peut laisser vide ou recréer l'ancienne contrainte si nécessaire
    }
};
