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
                if (!Schema::hasColumn('rapports', 'taux_actualisation')) {
                    $table->float('taux_actualisation')->default(0);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rapports', function (Blueprint $table) {
            $table->dropColumn('taux_actualisation');
        });
    }
};
