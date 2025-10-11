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
                if (!Schema::hasColumn('idees_projet', 'score_pertinence')) {
                    $table->float('score_pertinence')->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('idees_projet', function (Blueprint $table) {
            $table->dropColumn('score_pertinence');
        });
    }
};
