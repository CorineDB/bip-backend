<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('champs_sections', function (Blueprint $table) {
            // Ajouter la référence vers la section parent pour créer une hiérarchie
            $table->bigInteger('parentSectionId')->unsigned()->nullable()->after('documentId');
            $table->foreign('parentSectionId')->references('id')->on('champs_sections')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('champs_sections', function (Blueprint $table) {
            // Supprimer la clé étrangère et la colonne
            $table->dropForeign(['parentSectionId']);
            $table->dropColumn(['parentSectionId', 'niveau', 'chemin_hierarchique']);
        });
    }
};