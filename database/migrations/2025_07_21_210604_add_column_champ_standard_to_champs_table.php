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
        if (Schema::hasTable('champs')) {
            Schema::table('champs', function (Blueprint $table) {
                if (!Schema::hasColumn('champs', 'champ_standard')) {
                    $table->boolean('champ_standard')->default(false);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('champs')) {
            Schema::table('champs', function (Blueprint $table) {
                if (Schema::hasColumn('champs', 'champ_standard')) {
                    $table->dropColumn('champ_standard');
                }
            });
        }
    }
};
