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
        // Ajout des champs latitude et longitude aux tables departements, communes, arrondissements et villages
        Schema::table('departements', function (Blueprint $table) {
            $table->float('latitude', 10, 7)->default(0.0);
            $table->float('longitude', 10, 7)->default(0.0);
        });

        Schema::table('communes', function (Blueprint $table) {
            $table->float('latitude', 10, 7)->default(0.0);
            $table->float('longitude', 10, 7)->default(0.0);
        });

        Schema::table('arrondissements', function (Blueprint $table) {
            $table->float('latitude', 10, 7)->default(0.0);
            $table->float('longitude', 10, 7)->default(0.0);
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->float('latitude', 10, 7)->default(0.0);
            $table->float('longitude', 10, 7)->default(0.0);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Suppression des champs latitude et longitude des tables departements, communes, arrondissements et villages
        Schema::table('departements', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('communes', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('arrondissements', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('villages', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });

    }
};
