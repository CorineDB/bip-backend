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
        Schema::table('projets', function (Blueprint $table) {
            // Ajouter la référence vers la section parent pour créer une hiérarchie
            $table->longText('resume_tdr_prefaisabilite')->nullable();
            $table->longText('resume_tdr_faisabilite')->nullable();
            $table->json('info_cabinet_etude_prefaisabilite')->nullable();
            $table->json('info_cabinet_etude_faisabilite')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            $table->dropColumn('resume_tdr_prefaisabilite');
            $table->dropColumn('resume_tdr_faisabilite');
            $table->dropColumn('info_cabinet_etude_prefaisabilite');
            $table->dropColumn('info_cabinet_etude_faisabilite');
        });
    }
};
