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
        Schema::table('cibles', function (Blueprint $table) {
            if (Schema::hasColumn('cibles', "cible")) {
                $table->longText('cible')->change();
            }
            if (Schema::hasColumn('cibles', "slug")) {
                $table->longText('slug')->change();
            }
            if (!Schema::hasColumn('cibles', "oddId")) {
                $table->bigInteger('oddId')->nullable()->unsigned()->index();
                $table->foreign('oddId')->references('id')->on('odds')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cibles', function (Blueprint $table) {
            if (Schema::hasColumn('cibles', "cible")) {
                $table->longText('cible')->change();
            }
            if (Schema::hasColumn('cibles', "slug")) {
                $table->longText('slug')->change();
            }
            if (Schema::hasColumn('cibles', "oddId")) {
                // D'abord supprimer la clé étrangère
                $table->dropForeign(['oddId']);
                // Puis supprimer l'index associé
                $table->dropIndex(['oddId']);
                // Enfin supprimer la colonne
                $table->dropColumn('oddId');
            }
        });
    }
};
