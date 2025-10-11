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
                if (!Schema::hasColumn('rapports', 'investissement_initial')) {
                    $table->float('investissement_initial')->default(0);
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
            $table->dropColumn('investissement_initial');
        });
    }
};
