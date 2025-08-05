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
        if (Schema::hasTable('evaluations')) {
            Schema::table('evaluations', function (Blueprint $table) {
                if (!Schema::hasColumn('evaluations', 'statut')) {
                    $table->enum('statut', [-1, 0, 1])->default(-1);
                }

                if (!Schema::hasColumn('evaluations', 'id_evaluation')) {
                    $table->unsignedBigInteger('id_evaluation')->nullable()->index();
                    $table->foreign('id_evaluation')
                        ->references('id')
                        ->on('evaluations')
                        ->onDelete('cascade')
                        ->onUpdate('cascade');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('evaluations')) {
            Schema::table('evaluations', function (Blueprint $table) {
                if (Schema::hasColumn('evaluations', 'id_evaluation')) {
                    $table->dropForeign(['id_evaluation']);
                    $table->dropIndex(['id_evaluation']);
                    $table->dropColumn('id_evaluation');
                }
                if (Schema::hasColumn('evaluations', 'statut')) {
                    $table->dropColumn('statut');
                }
            });
        }
    }
};
