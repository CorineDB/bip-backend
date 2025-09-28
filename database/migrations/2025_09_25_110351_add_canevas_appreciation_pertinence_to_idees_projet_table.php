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
                if (!Schema::hasColumn('idees_projet', 'canevas_appreciation_pertinence')) {
                    $table->json('canevas_appreciation_pertinence')->nullable();
                }
                if (!Schema::hasColumn('idees_projet', 'canevas_climatique')) {
                    $table->json('canevas_climatique')->nullable();
                }
                if (!Schema::hasColumn('idees_projet', 'canevas_amc')) {
                    $table->json('canevas_amc')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('idees_projet')) {
            Schema::table('idees_projet', function (Blueprint $table) {
                if (Schema::hasColumn('idees_projet', 'canevas_appreciation_pertinence')) {
                    $table->dropColumn('canevas_appreciation_pertinence');
                }
                if (Schema::hasColumn('idees_projet', 'canevas_climatique')) {
                    $table->dropColumn('canevas_climatique');
                }
                if (Schema::hasColumn('idees_projet', 'canevas_amc')) {
                    $table->dropColumn('canevas_amc');
                }
            });
        }
    }
};
