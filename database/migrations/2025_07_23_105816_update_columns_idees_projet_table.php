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
        if (Schema::hasTable('idees_projet')) {
            Schema::table('idees_projet', function (Blueprint $table) {
                if (Schema::hasColumn('idees_projet', 'secteurId')) {
                    $table->bigInteger('secteurId')->nullable()->unsigned()->change();
                }

                if (!Schema::hasColumn('idees_projet', 'est_soumise')) {
                    $table->boolean('est_soumise')->default(false);
                }

                if (Schema::hasColumn('idees_projet', 'ministereId')) {
                    $table->bigInteger('ministereId')->nullable()->unsigned()->change();
                }

                if (Schema::hasColumn('idees_projet', 'categorieId')) {
                    $table->bigInteger('categorieId')->nullable()->unsigned()->change();
                }

                if (Schema::hasColumn('idees_projet', 'responsableId')) {
                    $table->bigInteger('responsableId')->nullable()->unsigned()->change();
                }

                if (Schema::hasColumn('idees_projet', 'demandeurId')) {
                    $table->bigInteger('demandeurId')->nullable()->unsigned()->change();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('idees_projet')) {
            Schema::table('idees_projet', function (Blueprint $table) {
                if (Schema::hasColumn('idees_projet', 'est_soumise')) {
                    $table->dropColumn('est_soumise');
                }
                if (Schema::hasColumn('idees_projet', 'secteurId')) {
                    $table->bigInteger('secteurId')->unsigned()->change();
                }

                if (Schema::hasColumn('idees_projet', 'ministereId')) {
                    $table->bigInteger('ministereId')->unsigned()->change();
                }

                if (Schema::hasColumn('idees_projet', 'categorieId')) {
                    $table->bigInteger('categorieId')->unsigned()->change();
                }

                if (Schema::hasColumn('idees_projet', 'responsableId')) {
                    $table->bigInteger('responsableId')->unsigned()->change();
                }

                if (Schema::hasColumn('idees_projet', 'demandeurId')) {
                    $table->bigInteger('demandeurId')->unsigned()->change();
                }
            });
        }
    }
};
