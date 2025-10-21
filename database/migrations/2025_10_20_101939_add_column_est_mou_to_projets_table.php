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
        Schema::table('projet', function (Blueprint $table) {
            //
        });
        if (Schema::hasTable('projets')) {
            Schema::table('projets', function (Blueprint $table) {
                if (!Schema::hasColumn('projets', 'est_mou')) {
                    $table->boolean('est_mou')->default("false");
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projet', function (Blueprint $table) {
            //
        });
    }
};
