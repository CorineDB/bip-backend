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
        Schema::table('tdrs', function (Blueprint $table) {
            $table->json('canevas_appreciation_tdr')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tdrs', function (Blueprint $table) {
            $table->dropColumn('canevas_appreciation_tdr');
        });
    }
};
