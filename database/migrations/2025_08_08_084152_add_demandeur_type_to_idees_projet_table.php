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
        if (Schema::hasTable('idees_projet')) {
            Schema::table('idees_projet', function (Blueprint $table) {
                //if (!Schema::hasColumn('idees_projet', 'demandeur_type')) {
                    $table->string('demandeur_type')->default('App\\Models\\User')->after('demandeurId');
                //}
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('idees_projet') && Schema::hasColumn('idees_projet', 'demandeur_type')) {
            Schema::table('idees_projet', function (Blueprint $table) {
                $table->dropColumn('demandeur_type');
            });
        }
    }
};
