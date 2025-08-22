<?php

use App\Services\Traits\HelperTrait;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->unsignedBigInteger('secteur_id')->nullable(true)->index();
            $table->foreign('secteur_id')->references('id')->on('secteurs')
                ->onDelete('cascade')
                ->onUpdate('cascade');

                // Suppression de l'ancienne contrainte unique si elle existe (à adapter selon ton cas)
                $this->dropUniqueIfExists(table: 'notations', constraint: 'unique_annotation_per_categorie_critere');

                // Contrainte unique composée
                $table->unique(['libelle', 'valeur', 'secteur_id', 'critere_id', 'categorie_critere_id'], 'unique_annotation_per_secteur_categorie_critere');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notations', function (Blueprint $table) {
            //
        });
    }
};
