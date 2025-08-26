<?php

use App\Services\Traits\HelperTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use HelperTrait;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('notations', function (Blueprint $table) {
            // Supprimer la clé étrangère et la colonne
            $table->dropForeign(['secteur_id']);
            $table->dropColumn('secteur_id');

            $table->unsignedBigInteger('secteur_id')->nullable(true)->index();
            $table->foreign('secteur_id')->references('id')->on('secteurs')
                ->onDelete('cascade')
                ->onUpdate('cascade');


            // Supprimer l’ancienne contrainte unique
            DB::statement('ALTER TABLE notations DROP CONSTRAINT IF EXISTS unique_annotation_per_categorie_critere');

            // Supprimer l’ancienne contrainte unique
            DB::statement('ALTER TABLE notations DROP CONSTRAINT IF EXISTS unique_annotation_per_secteur_categorie_critere');

            // Ajouter la nouvelle contrainte avec secteur_id
            DB::statement('
                ALTER TABLE notations
                ADD CONSTRAINT unique_annotation_per_categorie_critere
                UNIQUE (libelle, valeur, critere_id, categorie_critere_id, secteur_id)
            ');

            // Suppression de l'ancienne contrainte unique si elle existe (à adapter selon ton cas)
            //$this->dropUniqueIfExists(table: 'notations', constraint: 'unique_annotation_per_categorie_critere');

            // Contrainte unique composée
            //$table->unique(['libelle', 'valeur', 'secteur_id', 'critere_id', 'categorie_critere_id'], 'unique_annotation_per_secteur_categorie_critere');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notations', function (Blueprint $table) {
            // Supprimer la clé étrangère et la colonne
            $table->dropForeign(['secteur_id']);
            $table->dropColumn('secteur_id');
        });

        // Supprimer la contrainte incluant secteur_id
        DB::statement('ALTER TABLE notations DROP CONSTRAINT IF EXISTS unique_annotation_per_categorie_critere');

        // Remettre l’ancienne contrainte (sans secteur_id)
        // 👉 Récréer la contrainte unique d'origine (rollback)
        DB::statement('
        ALTER TABLE notations
        ADD CONSTRAINT unique_annotation_per_categorie_critere
        UNIQUE (libelle, valeur, critere_id, categorie_critere_id)
    ');
    }
};
