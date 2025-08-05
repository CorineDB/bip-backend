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
        if (Schema::hasTable('dpaf')) {
            Schema::table('dpaf', function (Blueprint $table) {
                if (!Schema::hasColumn('dpaf', 'id_ministere')) {
                    $table->unsignedBigInteger('id_ministere')->nullable()->index();
                    $table->foreign('id_ministere')->references('id')->on('organisations')
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
        if (Schema::hasTable('dpaf')) {
            Schema::table('dpaf', function (Blueprint $table) {
                if (Schema::hasColumn('dpaf', 'id_ministere')) {
                    $table->dropForeign(['id_ministere']);
                    $table->dropIndex(['id_ministere']);
                    $table->dropColumn('id_ministere');
                }
            });
        }
    }
};
