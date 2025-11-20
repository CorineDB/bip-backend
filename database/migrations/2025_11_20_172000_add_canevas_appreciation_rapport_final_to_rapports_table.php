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
        if (Schema::hasTable('rapports')) {
            Schema::table('rapports', function (Blueprint $table) {
                if (!Schema::hasColumn('rapports', 'canevas_appreciation_rapport_final')) {
                    $table->json('canevas_appreciation_rapport_final')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('rapports')) {
            Schema::table('rapports', function (Blueprint $table) {
                if (Schema::hasColumn('rapports', 'canevas_appreciation_rapport_final')) {
                    $table->dropColumn('canevas_appreciation_rapport_final');
                }
            });
        }
    }
};
