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
        if (Schema::hasTable('tdrs')) {
            Schema::table('tdrs', function (Blueprint $table) {
                if (!Schema::hasColumn('tdrs', 'canevas_appreciation_tdr')) {
                    $table->json('canevas_appreciation_tdr')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tdrs')) {
            Schema::table('tdrs', function (Blueprint $table) {
                if (Schema::hasColumn('tdrs', 'canevas_appreciation_tdr')) {
                    $table->dropColumn('canevas_appreciation_tdr');
                }
            });
        }
    }
};
