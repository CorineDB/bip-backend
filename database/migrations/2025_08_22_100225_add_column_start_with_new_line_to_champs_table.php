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
        Schema::table('champs', function (Blueprint $table) {
            // Ajouter la référence vers la section parent pour créer une hiérarchie
            $table->boolean('startWithNewLine')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('champs', function (Blueprint $table) {
            //
        });
    }
};
