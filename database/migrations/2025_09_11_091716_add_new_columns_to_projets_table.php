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
            $table->json('info_etude_prefaisabilite')->nullable()->before('mesures_adaptation')->comment("Info de l'etude de prefaisabilite");
            $table->json('info_etude_faisabilite')->nullable()->after('info_etude_prefaisabilite')->comment("Info de l'etude de faisabilite");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table) {

            if (Schema::hasColumn('projets', "info_etude_prefaisabilite")) {
                $table->dropColumn('info_etude_prefaisabilite');
            }
            if (Schema::hasColumn('projets', "info_etude_faisabilite")) {
                $table->dropColumn('info_etude_faisabilite');
            }

        });
    }
};
