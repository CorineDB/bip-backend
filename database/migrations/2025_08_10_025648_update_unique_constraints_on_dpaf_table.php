<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer les contraintes uniques en SQL brut (ignorer si n'existe pas)
        /*DB::statement('DROP INDEX IF EXISTS roles_nom_unique;');
        DB::statement('DROP INDEX IF EXISTS roles_slug_unique;');*/

        Schema::table('dpaf', function (Blueprint $table) {

            // Supprime les contraintes uniques (si elles existent, sinon ça peut générer une erreur)
            // Tu peux entourer de try/catch si besoin dans une migration brute SQL

            $table->dropUnique('dpaf_nom_unique');
            $table->dropUnique('dpaf_slug_unique');

            // Ajoute la contrainte unique composite
            $table->unique(['slug', 'id_ministere'], 'dpaf_id_ministere_slug_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('dpaf', function (Blueprint $table) {
            // Supprimer l'index unique composite ajouté
            $table->dropUnique('dpaf_id_ministere_slug_unique');

            // Remettre les contraintes uniques sur 'nom' et 'slug' (si besoin)
            $table->unique('nom', 'dpaf_nom_unique');
            $table->unique('slug', 'dpaf_slug_unique');
        });
    }
};
