<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer l’ancienne contrainte unique
        DB::statement('ALTER TABLE notations DROP CONSTRAINT IF EXISTS unique_annotation_per_categorie_critere');
        DB::statement('ALTER TABLE notations DROP CONSTRAINT IF EXISTS unique_annotation_per_secteur_categorie_critere');

        // Ajouter la nouvelle contrainte avec secteur_id
        DB::statement('
                ALTER TABLE notations
                ADD CONSTRAINT unique_annotation_per_categorie_critere
                UNIQUE (libelle, valeur, critere_id, categorie_critere_id, secteur_id)
            ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer la contrainte avec secteur_id
        DB::statement('ALTER TABLE notations DROP CONSTRAINT IF EXISTS unique_annotation_per_categorie_critere');

        // Restaurer l’ancienne contrainte (sans secteur_id)
        DB::statement('
            ALTER TABLE notations
            ADD CONSTRAINT unique_annotation_per_categorie_critere
            UNIQUE (libelle, valeur, critere_id, categorie_critere_id)
        ');
    }
};
