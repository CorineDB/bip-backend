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
        Schema::table('fichiers', function (Blueprint $table) {
            if (Schema::hasColumn('fichiers', "nom_stockage")) {
                $table->text('nom_stockage')->change();
            }
            if (Schema::hasColumn('fichiers', "chemin")) {
                $table->text('chemin')->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fichiers', function (Blueprint $table) {
            $table->string('nom_stockage', 255)->change();
            $table->string('chemin', 255)->change();
        });
    }
};
