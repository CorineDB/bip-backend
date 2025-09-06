<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('fichiers', function (Blueprint $table) {
            $table->foreignId('dossier_id')->nullable()->after('commentaire')->constrained('dossiers')->onDelete('set null');
            $table->index(['dossier_id', 'uploaded_by']); // Index pour optimiser les requêtes
        });
    }

    public function down()
    {
        Schema::table('fichiers', function (Blueprint $table) {
            $table->dropForeign(['dossier_id']);
            $table->dropIndex(['dossier_id', 'uploaded_by']);
            $table->dropColumn('dossier_id');
        });
    }
};