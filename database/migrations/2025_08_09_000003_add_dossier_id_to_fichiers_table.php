<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('fichiers')) {
            Schema::table('fichiers', function (Blueprint $table) {
                // Ajouter la colonne seulement si elle n'existe pas
                if (!Schema::hasColumn('fichiers', 'dossier_id')) {
                    $table->foreignId('dossier_id')->nullable()->after('commentaire')->constrained('dossiers')->onDelete('set null');
                }

                // Ajouter l'index seulement si il n'existe pas
                $indexes = Schema::getConnection()->getDoctrineSchemaManager()->listTableIndexes('fichiers');
                if (!array_key_exists('fichiers_dossier_id_uploaded_by_index', $indexes)) {
                    $table->index(['dossier_id', 'uploaded_by'], 'fichiers_dossier_id_uploaded_by_index'); // Index pour optimiser les requêtes
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('fichiers')) {
            Schema::table('fichiers', function (Blueprint $table) {
                if (Schema::hasColumn('fichiers', 'dossier_id')) {
                    $table->dropForeign(['dossier_id']);
                    $table->dropIndex(['dossier_id', 'uploaded_by']);
                    $table->dropColumn('dossier_id');
                }
            });
        }
    }
};
